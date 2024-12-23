<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use FPDF;
use Carbon\Carbon;

class PdfController extends Controller
{
  private $summaryTypes = [
    "detailed" => [
      "prompt" => "Thoroughly analyze the given text and provide the following in a detailed format:\n
                    1. **Main Points**: Extract and list the primary themes and ideas as clear bullet points.\n
                    2. **Key Findings**: Highlight essential data, conclusions, or insights using concise bullet points.\n
                    3. **Comprehensive Analysis**: Offer an in-depth discussion of the content, connecting ideas logically with detailed explanations, paragraphs, and bullet points as necessary.",
    ],
    "structured" => [
      "prompt" => "Craft a well-structured and organized summary of the given text with the following sections:\n
                    1. **Overview**: Provide a concise introduction summarizing the main topic and purpose of the text.\n
                    2. **Key Points**: Present the most important arguments or data as clear bullet points.\n
                    3. **Conclusion**: End with a meaningful takeaway or recommendation. Use bullet points and concise wording for clarity.",
    ],
    "short" => [
      "prompt" =>
        "Generate a highly concise summary of the content provided, focusing on the most critical aspects and keeping the length under 4 sentences. Use bullet points only if absolutely necessary for clarity.",
    ],
    "dummy" => [
      "prompt" =>
        "Simplify the given text as if explaining to a child. Break down complex concepts into plain and easy-to-understand language using clear sentences or bullet points. Ensure no technical jargon or advanced terms are used.",
    ],
    "extensive_researcher" => [
      "prompt" => "Analyze the content in great depth and provide a research-style summary with the following sections:\n
                    1. **Abstract**: A brief summary of the entire text.\n
                    2. **Introduction**: Explain the context and purpose of the content.\n
                    3. **Findings**: Break down key data, arguments, and evidence into bullet points.\n
                    4. **Discussion**: Explore the implications, connections, and conclusions in detail, supported by logical reasoning and examples.\n
                    5. **References**: Suggest potential related topics, studies, or further reading.",
    ],
    "creative" => [
      "prompt" =>
        "Reimagine the text into a creative and engaging format, such as a story, analogy, or dialogue. Ensure the core ideas and key points are preserved but presented in a way that captivates and simplifies understanding.",
    ],
    "persuasive" => [
      "prompt" =>
        "Summarize the text with a focus on persuasion. Highlight key arguments and evidence that support the main idea, using bullet points for clarity. End with a compelling conclusion that emphasizes the importance of the message.",
    ],
    "listicle" => [
      "prompt" =>
        "Turn the content into a listicle format, breaking it into numbered or bullet points for easy readability. Each point should be concise yet informative, summarizing key themes and ideas clearly.",
    ],
    "question_answer" => [
      "prompt" =>
        "Transform the content into a Q&A format. For each major idea, create a question and provide a concise yet informative answer. Ensure clarity and completeness in the responses.",
    ],
    "summary_with_examples" => [
      "prompt" =>
        "Summarize the given text, ensuring to include examples or case studies mentioned. Highlight key ideas using bullet points and provide examples to illustrate each point.",
    ],
    "emphasized_takeaways" => [
      "prompt" =>
        "Focus on delivering the top takeaways from the text. Summarize the key insights, lessons, or conclusions in bolded bullet points for easy identification.",
    ],
    "problem_solution" => [
      "prompt" => "Identify problems and solutions mentioned in the text. Structure the summary with the following sections:\n
                    1. **Problems Identified**: List each issue as bullet points.\n
                    2. **Proposed Solutions**: Provide corresponding solutions or recommendations for each problem.",
    ],
    "opinionated_summary" => [
      "prompt" =>
        "Summarize the content by including a critical perspective or opinion. Present the main points and key ideas, followed by an evaluation of the strengths and weaknesses of the content.",
    ],
  ];
  public function ConvertPDF(Request $request)
  {
    $request->validate([
      "pdf" => "required|file|mimes:pdf|max:10240",
      "from_page" => "required|integer|min:1",
      "to_page" => "nullable|integer|min:1",
      "summary_type" =>
        "required|string|in:" . implode(",", array_keys($this->summaryTypes)),
    ]);

    try {
      $pdfFile = $request->file("pdf");
      $fromPage = $request->input("from_page");
      $toPage = $request->input("to_page");
      $summaryType = $request->input("summary_type");

      $parser = new Parser();
      $pdf = $parser->parseFile($pdfFile->getPathname());
      $pages = $pdf->getPages();
      $totalPages = count($pages);
      $metaData = $pdf->getDetails();

      if (!$toPage || $toPage > $totalPages) {
        $toPage = $totalPages;
      }
      if ($toPage - $fromPage + 1 > 50) {
        $toPage = $fromPage + 49;
      }
      if ($fromPage > $toPage) {
        return response()->json(
          [
            "message" => "Invalid page range",
            "error" => "Ensure 'from_page' is less than or equal to 'to_page'.",
          ],
          422
        );
      }

      $pageSummaries = [];
      $extractedText = "";

      // Process pages
      for ($i = $fromPage - 1; $i < $toPage; $i++) {
        $pageNumber = $i + 1;
        $pageText = $pages[$i]->getText();
        $extractedText .= $pageText . "\n\n";

        // Get summary for this page
        $pageSummaries[$pageNumber] = $this->Summarize(
          $pageText,
          $metaData,
          $summaryType,
          $pageNumber
        );
      }

      // Store summary
      $filename = $this->storeSummary(
        $pageSummaries,
        $metaData,
        $pdfFile->getClientOriginalName(),
        $summaryType
      );

      return response()->json([
        "message" => "Text extracted and summarized successfully",
        "extracted_text" => $extractedText,
        "page_summaries" => $pageSummaries,
        "summary_file" => $filename,
      ]);
    } catch (\Exception $e) {
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

  private function storeSummary(
    $pageSummaries,
    $metaData,
    $originalFilename,
    $summaryType
  ) {
    $timestamp = Carbon::now()->format("Y-m-d_H-i-s");
    $filename = sprintf(
      "summary_%s_%s_%s.pdf",
      pathinfo($originalFilename, PATHINFO_FILENAME),
      $summaryType,
      $timestamp
    );

    $documentTitle = isset($metaData["title"])
      ? $metaData["title"]
      : "Untitled Document";

    // PDF generation
    $pdf = new FPDF();
    $pdf->SetMargins(20, 20, 20);
    $pdf->AddPage();

    // Text processing function
    function cleanText($text)
    {
      // Remove bold markers (text between **)
      $text = preg_replace("/\*\*(.*?)\*\*/", '<b>$1</b>', $text);

      // Replace unsupported characters and clean unwanted symbols
      $text = str_replace(
        ["⁻", "⁵", "⁶", "Ωm", "@", "-", "_", "\""],
        ["-", "5", "6", "Ohm", "@", "-", "_", "\""],
        $text
      );

      // Allow alphabetic characters, digits, and special symbols
      $text = preg_replace("/[^a-zA-Z0-9\s\*\-@_\".,;?!()&<b>]/", "", $text);

      return trim($text);
    }

    // Header
    $pdf->SetFont("Arial", "B", 24);
    $pdf->Cell(0, 15, "Document Analysis Summary", 0, 1, "C");
    $pdf->SetFont("Arial", "B", 16);
    $pdf->Cell(0, 10, $documentTitle, 0, 1, "C");
    $pdf->Ln(10);

    // Metadata
    $pdf->SetFont("Arial", "B", 14);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(0, 10, "Document Information", 1, 1, "L", true);

    foreach ($metaData as $key => $value) {
      if (is_string($value)) {
        $pdf->SetFont("Arial", "B", 11);
        $pdf->Cell(50, 8, ucfirst($key) . ":", 1);
        $pdf->SetFont("Arial", "", 11);
        $pdf->Cell(0, 8, $value, 1);
        $pdf->Ln();
      }
    }
    $pdf->Ln(10);

    // Process summaries
    foreach ($pageSummaries as $pageNumber => $summary) {
      $pdf->SetFont("Arial", "B", 14);
      $pdf->SetFillColor(240, 240, 240);
      $pdf->Cell(0, 10, "Page $pageNumber Summary", 1, 1, "L", true);

      $lines = explode("\n", $summary);
      foreach ($lines as $line) {
        $line = cleanText($line);

        if (preg_match("/^(\s*)\*\s/", $line, $matches)) {
          $indent = strlen($matches[1]);
          $pdf->SetX(20 + $indent * 4);
          $pdf->SetFont("Arial", "", 11);
          $pdf->Cell(5, 8, chr(149), 0);
          $text = ltrim(preg_replace("/^(\s*)\*\s/", "", $line));

          if (strpos($text, ":") !== false) {
            list($label, $value) = explode(":", $text, 2);
            $pdf->SetFont("Arial", "B", 11);
            $pdf->Write(8, trim($label) . ": ");
            $pdf->SetFont("Arial", "", 11);
            $pdf->Write(8, trim($value));
          } else {
            $pdf->Write(8, $text);
          }
          $pdf->Ln();
        } else {
          $pdf->SetX(20);
          if (preg_match('/^([^:]+):(.*)$/', $line, $matches)) {
            $pdf->SetFont("Arial", "B", 11);
            $pdf->Write(8, trim($matches[1]) . ": ");
            $pdf->SetFont("Arial", "", 11);
            $pdf->Write(8, trim($matches[2]));
            $pdf->Ln();
          } else {
            $pdf->MultiCell(0, 8, $line);
          }
        }
      }
      $pdf->Ln(5);
    }

    // Footer
    $pdf->SetY(-35);
    $pdf->SetFont("Arial", "B", 12); // Bold and bigger font
    $pdf->Cell(
      0,
      10,
      "Generated by Summarizer on " .
        Carbon::now()->format('F j, Y \a\t g:i A'),
      0,
      1,
      "C"
    );
    $pdf->SetFont("Arial", "I", 10);
    $pdf->Cell(0, 10, "Page " . $pdf->PageNo(), 0, 0, "C");

    // Output the PDF to the specified location
    $pdf->Output(
      storage_path(
        "app/summaries/" . pathinfo($filename, PATHINFO_FILENAME) . ".pdf"
      ),
      "F"
    );

    return $filename;
  }
}
