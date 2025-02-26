<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;
use FPDF;
use Carbon\Carbon;
use voku\helper\UTF8;
use App\Models\PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use ForceUTF8\Encoding;
use Symfony\Component\String\UnicodeString;
use App\Http\Controllers\PDFConfingController;
use Illuminate\Http\JsonResponse;

class PdfController extends Controller
{
  private $summaryTypes;
  private $themes;
  private $supabaseUrl;
  private $apiKey;
  private $bucketName;

  public function __construct()
  {
    $this->summaryTypes = PDFConfingController::getSummaryTypes();
    $this->themes = PDFConfingController::getThemes();
    $this->supabaseUrl = "https://ehasinzstggpytkeeqlm.supabase.co";
    $this->apiKey = env("SUPABASE_API_KEY");
    $this->bucketName = env("SUPABASE_BUCKET_NAME", "Brevity");
  }

  public function ConvertPDF(Request $request): JsonResponse
  {
    
    $user = Auth::user();
    if ($user->max_credits - $user->used_credits <= 0) {
      return response()->json(
        [
          "message" => "Insufficient credits",
          "error" =>
            "You have used all your credits. Please purchase more to continue.",
          "remaining_credits" => 0,
        ],
        403
      );
    }

    // Validation
    $request->validate([
      "pdf" => "required|file|mimes:pdf|max:20240",
      "from_page" => "required|integer|min:1",
      "to_page" => "nullable|integer|min:1",
      "summary_type" =>
        "required|string|in:" . implode(",", array_keys($this->summaryTypes)),
      "theme" =>
        "nullable|string|in:" . implode(",", array_keys($this->themes)),
    ]);

    try {
      $pdfFile = $request->file("pdf");

      // File validation
      if (!$pdfFile || !$pdfFile->isValid()) {
        \Log::error("PDF Upload Failed", [
          "error" => $pdfFile
            ? $pdfFile->getErrorMessage()
            : "No file uploaded",
          "mime" => $pdfFile ? $pdfFile->getMimeType() : "N/A",
        ]);
        return response()->json(
          [
            "message" => "Invalid PDF file",
            "error" => "File upload failed or corrupted",
          ],
          422
        );
      }

      // Content validation
      $fileContent = file_get_contents($pdfFile->getPathname());
      if (!$fileContent || strlen($fileContent) < 100) {
        return response()->json(
          [
            "message" => "Invalid PDF content",
            "error" => "File appears to be empty or corrupted",
          ],
          422
        );
      }

      if (substr($fileContent, 0, 4) !== "%PDF") {
        return response()->json(
          [
            "message" => "Invalid PDF format",
            "error" => "File is not a valid PDF document",
          ],
          422
        );
      }
      if (!$request->hasFile("pdf") || !$request->file("pdf")->isValid()) {
        \Log::error("PDF Upload Failed", [
          "error" => $request->hasFile("pdf")
            ? $request->file("pdf")->getErrorMessage()
            : "No file received",
          "uploadedSize" => $request->header("Content-Length"),
          "maxSize" => ini_get("upload_max_filesize"),
        ]);

        return response()->json(
          [
            "message" => "The pdf failed to upload.",
            "errors" => [
              "pdf" => [
                "File upload failed. Maximum allowed size is " .
                ini_get("upload_max_filesize"),
              ],
            ],
          ],
          422
        );
      }

      // File naming
      $userId = Auth::id();
      $username = Auth::user()->name;
      $timestamp = Carbon::now()->format("Y-m-d_H-i-s");
      $originalFileName = strtolower(
        preg_replace("/[^a-zA-Z0-9._-]/", "", $pdfFile->getClientOriginalName())
      );
      if (strlen($originalFileName) > 100) {
        $originalFileName = substr($originalFileName, 0, 100);
      }

      $uniqueFileName = sprintf(
        "%s_%s_%s_%s",
        $userId,
        Str::slug($username),
        $timestamp,
        $originalFileName
      );

      // Page processing
      $fromPage = $request->input("from_page");
      $toPage = $request->input("to_page");
      $summaryType = $request->input("summary_type");

      try {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfFile->getPathname());
      } catch (\Exception $e) {
        \Log::error("PDF Parsing Error", [
          "error" => $e->getMessage(),
          "file" => $originalFileName,
        ]);
        return response()->json(
          [
            "message" => "PDF parsing error",
            "error" =>
              "Unable to parse the PDF file. Please ensure it's a valid PDF document.",
          ],
          422
        );
      }

      $pages = $pdf->getPages();
      $totalPages = count($pages);
      $metaData = $pdf->getDetails();

      // Page range validation
      if (!$toPage || $toPage > $totalPages) {
        $toPage = $totalPages;
      }

      if ($toPage - $fromPage + 1 > 20) {
        $toPage = $fromPage + 19;
      }

      if ($fromPage > $totalPages || $fromPage > $toPage) {
        return response()->json(
          [
            "message" => "Invalid page range",
            "error" => "Ensure 'from_page' is less than or equal to 'to_page'.",
          ],
          422
        );
      }

      // Process pages
      $pageSummaries = [];
      $extractedText = "";

      for ($i = $fromPage - 1; $i < $toPage; $i++) {
        $pageNumber = $i + 1;
        try {
          $pageText = $pages[$i]->getText();
          $extractedText .= $pageText . "\n\n";
          $pageSummaries[$pageNumber] = $this->Summarize(
            $pageText,
            $metaData,
            $summaryType,
            $pageNumber
          );
        } catch (\Exception $e) {
          \Log::error("Page Processing Error", [
            "page" => $pageNumber,
            "error" => $e->getMessage(),
          ]);
          return response()->json(
            [
              "message" => "Error processing page $pageNumber",
              "error" => $e->getMessage(),
            ],
            500
          );
        }
      }

      // Generate summary PDF
      $summaryFileName = sprintf(
        "summary_%s_%s_%s_%s.pdf",
        $userId,
        Str::slug($username),
        pathinfo($originalFileName, PATHINFO_FILENAME),
        $timestamp
      );

      try {
        $summaryPath = $this->storeSummary(
          $pageSummaries,
          $metaData,
          $summaryFileName,
          $summaryType
        );

        // Create database record
        PDF::create([
          "user_id" => $userId,
          "original_filename" => $originalFileName,
          "summary_path" => $summaryPath,
          "summary_type" => $summaryType,
          "pages_processed" => $toPage - $fromPage + 1,
        ]);

        $user->increment("used_credits");

        return response()->json([
          "message" => "Text extracted and summarized successfully",
          "extracted_text" => $extractedText,
          "page_summaries" => $pageSummaries,
          "summary_file" => $summaryPath,
          "remaining_credits" => $user->max_credits - $user->used_credits,
        ]);
      } catch (\Exception $e) {
        \Log::error("Summary Generation Error", [
          "error" => $e->getMessage(),
          "trace" => $e->getTraceAsString(),
        ]);
        return response()->json(
          [
            "message" => "Error generating summary",
            "error" => $e->getMessage(),
          ],
          500
        );
      }
    } catch (\Exception $e) {
      \Log::error("PDF Processing Error", [
        "error" => $e->getMessage(),
        "trace" => $e->getTraceAsString(),
      ]);
      return response()->json(
        [
          "message" => "Error processing PDF",
          "error" => $e->getMessage(),
        ],
        500
      );
    }
  }

  public function Summarize($text, $metaData, $summaryType, $pageNumber)
  {
    $promptText = $this->summaryTypes[$summaryType]["prompt"];
    $promptText .= "\n\nAnalyzing Page {$pageNumber}:\n";

    $postData = json_encode([
      "contents" => [
        [
          "parts" => [["text" => $promptText], ["text" => $text]],
        ],
      ],
    ]);

    $ai_apikey = env("AI_API_KEY");
    $ai_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$ai_apikey";

    $ch = curl_init($ai_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      curl_close($ch);
      return "Error generating summary for page $pageNumber";
    }

    curl_close($ch);
    $responseData = json_decode($response, true);

    if (isset($responseData["candidates"][0]["content"]["parts"][0]["text"])) {
      return $responseData["candidates"][0]["content"]["parts"][0]["text"];
    }

    return "Unable to generate summary for page $pageNumber";
  }

  private function cleanText($text)
  {
    if (empty($text)) {
      return "";
    }

    $encoding = mb_detect_encoding(
      $text,
      ["UTF-8", "ISO-8859-1", "Windows-1252"],
      true
    );
    if ($encoding !== "UTF-8") {
      $text = mb_convert_encoding($text, "UTF-8", $encoding);
    }

    $text = Encoding::toUTF8($text);

    $text = UTF8::cleanup($text);
    $text = UTF8::fix_simple_utf8($text);
    $text = (new UnicodeString($text))
      ->normalize(UnicodeString::NFKC)
      ->toString();

    $replacements = [
      "â€“" => "—",
      "â€" => "—",
      "\xe2\x80\x94" => "—",
      "â€" => "–",
      "\xe2\x80\x93" => "–",
      "â€œ" => "\"",
      "â€" => "\"",
      "â€˜" => "'",
      "â€™" => "'",
      "â€™" => "'",
      "â€¦" => "…",
      "—" => "-",
      "–" => "-",
      "\"" => "\"",
      "â€•" => "'",
      "â€˜" => "'",
      "…" => "...",
    ];

    $text = str_replace(
      array_keys($replacements),
      array_values($replacements),
      $text
    );

    $text = preg_replace_callback(
      '/#{1,6}\s+([^\n]+)/',
      function ($matches) {
        $level = strlen($matches[0]) - strlen(trim($matches[1]));
        return "{{H" .
          $level .
          "}}" .
          trim($matches[1]) .
          "{{/H" .
          $level .
          "}}";
      },
      $text
    );

    $text = preg_replace("/\*\*([^*]+)\*\*/", '{{B}}$1{{/B}}', $text);
    $text = preg_replace("/\*([^*]+)\*/", '{{I}}$1{{/I}}', $text);

    $text = preg_replace("/[^\p{L}\p{N}\s\p{P}\p{Sm}\p{Sc}]/u", "", $text);

    return trim($text);
  }

  private function writeFormattedText($pdf, $text, $themeColors)
  {
    // Define header sizes based on level
    $headerSizes = [
      1 => ["size" => 24, "spacing" => 15],
      2 => ["size" => 20, "spacing" => 12],
      3 => ["size" => 16, "spacing" => 10],
      4 => ["size" => 14, "spacing" => 8],
      5 => ["size" => 12, "spacing" => 6],
      6 => ["size" => 11, "spacing" => 5],
    ];

    // Split text into lines
    $lines = explode("\n", $text);

    foreach ($lines as $line) {
      // Check for headers
      if (preg_match('/{{H(\d)}}(.+?){{\/H\1}}/', $line, $matches)) {
        $level = $matches[1];
        $headerText = $matches[2];

        $pdf->SetFont("Arial", "B", $headerSizes[$level]["size"]);
        $pdf->SetTextColor(...$themeColors["header_text_color"]);
        $pdf->Ln($headerSizes[$level]["spacing"]);
        $pdf->Write($headerSizes[$level]["spacing"], $headerText);
        $pdf->Ln($headerSizes[$level]["spacing"]);

        // Reset text color
        $pdf->SetTextColor(...$themeColors["text_color"]);
        continue;
      }

      // Process regular text with inline formatting
      $segments = preg_split("/{{[BI]}}|{{\/[BI]}}/", $line);
      $format = "";

      foreach ($segments as $index => $segment) {
        // Determine text format
        if (strpos($line, "{{B}}") !== false) {
          $format = $index % 2 === 1 ? "B" : "";
        } elseif (strpos($line, "{{I}}") !== false) {
          $format = $index % 2 === 1 ? "I" : "";
        }

        $pdf->SetFont("Arial", $format, 11);
        if (trim($segment) !== "") {
          $pdf->Write(8, $segment);
        }
      }
      $pdf->Ln();
    }
  }
  //superbase Methods For File Storage
  private function uploadToSupabase($tempFile, $path)
  {
    $url = "https://ehasinzstggpytkeeqlm.supabase.co/storage/v1/object/{$this->bucketName}/$path";
    try {
      $apiKey = trim(env("SUPABASE_API_KEY", ""));

      $response = Http::withHeaders([
        "Authorization" => "Bearer " . $apiKey,
        "Content-Type" => "application/pdf",
      ])
        ->withBody(file_get_contents($tempFile), "application/pdf")
        ->post($url);

      if (!$response->successful()) {
        \Log::error("Supabase upload failed", [
          "status" => $response->status(),
          "body" => $response->body(),
        ]);
        throw new \Exception("Upload failed: " . $response->body());
      }
      return $path;
    } catch (\Exception $e) {
      \Log::error("Upload error", ["error" => $e->getMessage()]);
      throw $e;
    }
  }

  private function downloadFile($path)
  {
    try {
      $bucketName = trim(env("SUPABASE_BUCKET_NAME", ""));

      $url = "https://ehasinzstggpytkeeqlm.supabase.co/storage/v1/object/public/{$this->bucketName}/{$path}";
      $apiKey = trim(env("SUPABASE_API_KEY", ""));

      $response = Http::withHeaders([
        "Authorization" => "Bearer " . $apiKey,
      ])->get($url);

      if (!$response->successful()) {
        \Log::error("Supabase upload failed", [
          "status" => $response->status(),
          "body" => $response->body(),
          "url" => $url,
          "bucketName" => $bucketName,
        ]);
        throw new \Exception("Download failed: " . $response->body());
      }

      return $response->body();
    } catch (\Exception $e) {
      \Log::error("Download error", ["error" => $e->getMessage()]);
      throw $e;
    }
  }
  private function deleteFromSupabase($path)
  {
    $url = "https://ehasinzstggpytkeeqlm.supabase.co/storage/v1/object/{$this->bucketName}/{$path}";
    try {
      $apiKey = trim(env("SUPABASE_API_KEY", ""));

      $response = Http::withHeaders([
        "Authorization" => "Bearer " . $apiKey,
      ])->delete($url);

      if (!$response->successful()) {
        \Log::error("Supabase delete failed", [
          "status" => $response->status(),
          "body" => $response->body(),
        ]);
        throw new \Exception("Delete failed: " . $response->body());
      }
    } catch (\Exception $e) {
      \Log::error("Delete error", ["error" => $e->getMessage()]);
      throw $e;
    }
  }

  private function storeSummary(
    $pageSummaries,
    $metaData,
    $filename,
    $summaryType
  ) {
    $userId = Auth::id();
    $path = "pdfs/summaries/{$userId}/{$filename}";
    $theme = request("theme", "clean_light");
    $themeColors = $this->themes[$theme];

    // Initialize PDF
    $pdf = new FPDF();
    $pdf->SetMargins(25, 25, 25);
    $pdf->AddPage();

    $pdf->SetFillColor(...$themeColors["header_bg"]);
    $pdf->Rect(0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight(), "F");

    $pdf->SetFillColor(
      ...$themeColors["accent_color"] ?? $themeColors["header_text_color"]
    );
    $pdf->Rect(0, 0, $pdf->GetPageWidth(), 3, "F");

    $originalFile = request()->file("pdf");
    $title = $originalFile
      ? pathinfo($originalFile->getClientOriginalName(), PATHINFO_FILENAME)
      : pathinfo($filename, PATHINFO_FILENAME);
    $title = ucwords(str_replace(["-", "_"], " ", $title));

    $titleText = "{{H1}}" . $title . "{{/H1}}";
    $this->writeFormattedText($pdf, $titleText, $themeColors);

    $subtitleText = "{{H2}}Summary Analysis{{/H2}}";
    $this->writeFormattedText($pdf, $subtitleText, $themeColors);

    if (isset($metaData["pageCount"])) {
      $pdf->SetFont("Arial", "I", 10);
      $pdf->SetTextColor(...$themeColors["text_color"]);
      $metadataText = "{{I}}Total Pages: " . $metaData["pageCount"] . "{{/I}}";
      $this->writeFormattedText($pdf, $metadataText, $themeColors);
    }

    $pdf->Ln(10);

    // Process each page summary
    foreach ($pageSummaries as $pageNumber => $summary) {
      if ($pdf->GetY() > $pdf->GetPageHeight() - 80) {
        $pdf->AddPage();
        $pdf->SetFillColor(...$themeColors["header_bg"]);
        $pdf->Rect(0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight(), "F");

      }

      $headerY = $pdf->GetY();
      $pdf->SetFillColor(
        ...$themeColors["accent_color"] ?? $themeColors["header_text_color"]
      );
      $pdf->Rect(20, $headerY, 4, 8, "F");

      $pdf->SetFillColor(
        ...$themeColors["secondary_color"] ?? $themeColors["header_bg"]
      );
      $pdf->Rect(24, $headerY, $pdf->GetPageWidth() - 44, 8, "F");

      // Format page header using your existing system
      $headerText = "{{H3}}Page " . $pageNumber . "{{/H3}}";
      $this->writeFormattedText($pdf, $headerText, $themeColors);

      // Add some spacing
      $pdf->Ln(5);

      // Process and write the summary content
      $cleanedText = $this->cleanText($summary);
      $pdf->SetX($pdf->GetX() + 5); // Add left padding
      $this->writeFormattedText($pdf, $cleanedText, $themeColors);

      $pdf->Ln(15); // Space between summaries
    }

    if ($pdf->GetY() < $pdf->GetPageHeight() - 40) {
      $this->addFooter($pdf, $themeColors);
    }

    $tempPath = tempnam(sys_get_temp_dir(), "pdf_");
    $pdf->Output($tempPath, "F");

    try {
      $uploadedPath = $this->uploadToSupabase(
        new \Illuminate\Http\File($tempPath),
        $path
      );
      unlink($tempPath);
      return $uploadedPath;
    } catch (\Exception $e) {
      unlink($tempPath);
      throw $e;
    }
  }

  private function addFooter($pdf, $themeColors)
  {
    $app_name = env("APP_NAME");
    $footerY = $pdf->GetPageHeight() - 30;

    // Footer separator line
    $pdf->SetDrawColor(...$themeColors["line_color"] ?? [200, 200, 200]);
    $pdf->Line(20, $footerY - 5, $pdf->GetPageWidth() - 20, $footerY - 5);

    // Footer text using formatting
    $pdf->SetY($footerY);
    $footerText =
      "{{I}}Generated by " .
      $app_name .
      " on " .
      Carbon::now()->format('F j, Y \a\t g:i A') .
      "\n" .
      "Page " .
      $pdf->PageNo() .
      "{{/I}}";
    $this->writeFormattedText($pdf, $footerText, $themeColors);
  }
  public function getUserPDFs(): JsonResponse
  {
    try {
      $userId = Auth::id();
      $pdfs = PDF::where("user_id", $userId)
        ->select(
          "id",
          "original_filename",
          "summary_path",
          "summary_type",
          "pages_processed",
          "created_at"
        )
        ->orderBy("created_at", "desc")
        ->get();
      return response()->json([
        "pdfs" => $pdfs,
        "message" => "PDFs retrieved successfully",
      ]);
    } catch (\Exception $e) {
      report($e);
      return response()->json(
        [
          "message" => "Failed to retrieve PDFs",
        ],
        500
      );
    }
  }

  public function downloadPDF($id)
  {
    try {
      $userId = Auth::id();
      $pdf = PDF::where("id", $id)
        ->where("user_id", $userId)
        ->firstOrFail();

      $content = $this->downloadFile($pdf->summary_path);

      return response($content)
        ->header("Content-Type", "application/pdf")
        ->header(
          "Content-Disposition",
          'attachment; filename="' . $pdf->original_filename . '"'
        );
    } catch (\Exception $e) {
      return response()->json(
        ["message" => "Failed to download PDF: " . $e->getMessage()],
        500
      );
    }
  }

  public function deletePDF($id)
  {
    try {
      $userId = Auth::id();
      $pdf = PDF::where("id", $id)
        ->where("user_id", $userId)
        ->firstOrFail();

      $this->deleteFromSupabase($pdf->summary_path);
      $pdf->delete();

      return response()->json(["message" => "PDF deleted successfully"]);
    } catch (\Exception $e) {
      return response()->json(
        ["message" => "Failed to delete PDF: " . $e->getMessage()],
        500
      );
    }
  }
}
