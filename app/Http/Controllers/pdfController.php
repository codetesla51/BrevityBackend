<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use FPDF;
use Carbon\Carbon;
use voku\helper\UTF8;

class PdfController extends Controller
{
private $summaryTypes = [
    "detailed" => [
      "prompt" => "Provide a comprehensive analysis of the text with these sections:\n
                    1. **Executive Summary**: A brief overview of the main content (2-3 sentences).\n
                    2. **Key Themes**: Identify and explain the major themes, supported by evidence from the text.\n
                    3. **Critical Analysis**: Break down important arguments, data, or findings.\n
                    4. **Notable Quotes**: Extract and analyze significant quotes or statements.\n
                    5. **Implications**: Discuss the broader implications and potential applications.\n
                    6. **Conclusions**: Synthesize the main takeaways and their significance.\n
                    **Note**: Ensure clarity and avoid excessive details. Focus on key takeaways and the significance of themes.",
    ],
    "structured" => [
      "prompt" => "Create a clear, structured analysis with:\n
                    1. **Context**: Brief background and relevance of the content.\n
                    2. **Main Arguments**: Analyze key points with supporting evidence.\n
                    3. **Supporting Data**: Present relevant statistics, examples, or case studies.\n
                    4. **Key Insights**: Extract valuable insights and their practical applications.\n
                    5. **Action Items**: List concrete takeaways or recommendations.\n
                    **Note**: Avoid overloading with data. Emphasize key arguments and their actionable outcomes.",
    ],
    "short" => [
      "prompt" => "Create a focused summary that:\n
                    1. Captures the essence in 2-3 powerful sentences\n
                    2. Highlights the single most important insight\n
                    3. Identifies one key actionable takeaway\n
                    Keep everything clear, direct, and impactful.\n
                    **Note**: Be concise and impactful. Avoid unnecessary details.",
    ],
    "dummy" => [
      "prompt" => "Explain this content in simple terms:\n
                    1. Use everyday examples and comparisons\n
                    2. Break complex ideas into simple steps\n
                    3. Include relatable analogies\n
                    4. Use clear, friendly language\n
                    5. Add engaging examples that a 12-year-old would understand\n
                    **Note**: Avoid jargon. Keep it conversational and relatable.",
    ],
    "extensive_researcher" => [
      "prompt" => "Conduct a thorough academic analysis:\n
                    1. **Abstract**: Concise overview of key findings (150 words)\n
                    2. **Introduction**: Context, objectives, and significance\n
                    3. **Methodology**: Approach and framework used in the content\n
                    4. **Results Analysis**: Detailed examination of main findings\n
                    5. **Discussion**: Critical evaluation and implications\n
                    6. **Future Directions**: Potential areas for further exploration\n
                    7. **Key References**: Related works and supporting materials\n
                    **Note**: Ensure depth in analysis but avoid getting bogged down in overly technical details.",
    ],
    "creative" => [
      "prompt" => "Transform this content creatively:\n
                    1. Create an engaging narrative or story\n
                    2. Use vivid metaphors and analogies\n
                    3. Include dialogue or character perspectives\n
                    4. Add descriptive scenarios\n
                    5. Maintain core message while making it memorable\n
                    Focus on engagement while preserving accuracy.\n
                    **Note**: Maintain balance between creativity and core message integrity.",
    ],
    "persuasive" => [
      "prompt" => "Create a compelling argument:\n
                    1. **Opening Hook**: Start with an attention-grabbing statement\n
                    2. **Context**: Frame the importance of the topic\n
                    3. **Evidence**: Present strongest supporting points\n
                    4. **Counter-Arguments**: Address potential objections\n
                    5. **Call to Action**: End with clear, motivating conclusion\n
                    **Note**: Be clear and direct. Avoid overly aggressive tone.",
    ],
    "listicle" => [
      "prompt" => "Transform into an engaging list format:\n
                    1. Create a compelling headline\n
                    2. Break content into 5-10 clear points\n
                    3. Add brief explanation for each point\n
                    4. Include relevant examples\n
                    5. End with a practical conclusion\n
                    Make each point clear, actionable, and memorable.\n
                    **Note**: Ensure each point is distinct and easy to follow.",
    ],
    "question_answer" => [
      "prompt" => "Create an insightful Q&A format:\n
                    1. Start with fundamental questions\n
                    2. Progress to more complex inquiries\n
                    3. Include practical application questions\n
                    4. Address common misconceptions\n
                    5. End with forward-looking questions\n
                    Ensure answers are clear, complete, and connected.\n
                    **Note**: Keep the questions and answers clear and logical, addressing the most important aspects first.",
    ],
    "summary_with_examples" => [
      "prompt" => "Create an example-rich summary:\n
                    1. **Main Concept**: Explain with real-world example\n
                    2. **Key Points**: Each supported by practical case\n
                    3. **Applications**: Show how ideas work in practice\n
                    4. **Scenarios**: Include relevant use cases\n
                    5. **Practical Tips**: Add actionable examples\n
                    **Note**: Provide diverse examples that demonstrate various applications of the concepts.",
    ],
    "emphasized_takeaways" => [
      "prompt" => "Extract and emphasize key learnings:\n
                    1. **Core Message**: The single most important point\n
                    2. **Critical Insights**: 3-5 key realizations\n
                    3. **Practical Applications**: How to use these insights\n
                    4. **Action Items**: Specific, implementable steps\n
                    5. **Success Metrics**: How to measure implementation\n
                    **Note**: Focus on clear and actionable takeaways. Avoid abstract concepts.",
    ],
    "problem_solution" => [
      "prompt" => "Analyze problems and solutions thoroughly:\n
                    1. **Problem Context**: Background and significance\n
                    2. **Key Challenges**: Detailed problem breakdown\n
                    3. **Solution Framework**: Comprehensive approach\n
                    4. **Implementation Steps**: Practical action plan\n
                    5. **Success Criteria**: Measuring effectiveness\n
                    6. **Risk Mitigation**: Addressing potential issues\n
                    **Note**: Focus on providing clear and actionable solutions with measurable success criteria.",
    ],
    "opinionated_summary" => [
      "prompt" => "Provide critical analysis with perspective:\n
                    1. **Content Overview**: Objective summary\n
                    2. **Strengths**: What works well and why\n
                    3. **Limitations**: Areas for improvement\n
                    4. **Alternative Views**: Different perspectives\n
                    5. **Recommendations**: Suggested enhancements\n
                    Support all opinions with clear reasoning.\n
                    **Note**: Be respectful and balanced when discussing limitations and alternative views.",
    ],
    "objective_questions" => [
      "prompt" => "Generate multiple-choice questions from the content:\n
                    1. **Core Concepts**: Create questions targeting key ideas\n
                    2. **Options**: Provide 4 plausible options (A, B, C, D) for each question\n
                    3. **Correct Answer**: Indicate the correct option\n
                    4. **Difficulty Levels**: Include questions of varying difficulty (easy, medium, hard)\n
                    5. **Clarity**: Ensure questions and options are unambiguous\n
                    Example:\n
                    Q1: What is the main theme of the text?\n
                    A. Theme 1\n
                    B. Theme 2 (Correct Answer)\n
                    C. Theme 3\n
                    D. Theme 4\n
                    **Note**: Ensure questions are clear and answer options are equally plausible.",
    ],
];

  private $themes = [
    "default" => [
      "header_bg" => [240, 240, 240],
      "text_color" => [0, 0, 0],
      "header_text_color" => [0, 0, 0],
      "line_color" => [200, 200, 200],
    ],
    "dark" => [
      "header_bg" => [50, 50, 50],
      "text_color" => [0, 0, 0],
      "header_text_color" => [255, 255, 255],
      "line_color" => [100, 100, 100],
    ],
    "blue" => [
      "header_bg" => [235, 245, 255],
      "text_color" => [0, 0, 0],
      "header_text_color" => [0, 51, 153],
      "line_color" => [200, 220, 255],
    ],
    "professional" => [
      "header_bg" => [245, 245, 245],
      "text_color" => [44, 62, 80],
      "header_text_color" => [52, 73, 94],
      "line_color" => [189, 195, 199],
    ],
    "modern" => [
      "header_bg" => [236, 240, 241],
      "text_color" => [46, 64, 82],
      "header_text_color" => [41, 128, 185],
      "line_color" => [189, 195, 199],
    ],
    "warm" => [
      "header_bg" => [255, 249, 235],
      "text_color" => [44, 62, 80],
      "header_text_color" => [211, 84, 0],
      "line_color" => [245, 176, 65],
    ],
    "nature" => [
      "header_bg" => [241, 248, 233],
      "text_color" => [46, 64, 82],
      "header_text_color" => [39, 174, 96],
      "line_color" => [46, 204, 113],
    ],
    "elegant" => [
      "header_bg" => [250, 250, 250],
      "text_color" => [44, 62, 80],
      "header_text_color" => [142, 68, 173],
      "line_color" => [155, 89, 182],
    ],
    "tech" => [
      "header_bg" => [236, 240, 241],
      "text_color" => [44, 62, 80],
      "header_text_color" => [52, 152, 219],
      "line_color" => [41, 128, 185],
    ],
    "minimal" => [
      "header_bg" => [255, 255, 255],
      "text_color" => [44, 62, 80],
      "header_text_color" => [44, 62, 80],
      "line_color" => [189, 195, 199],
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
      "theme" =>
        "nullable|string|in:" . implode(",", array_keys($this->themes)),
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

    $text = UTF8::cleanup($text);
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
      "'" => "'",
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

    $theme = request("theme", "default");
    $themeColors = $this->themes[$theme];

    $pdf = new FPDF();
    $pdf->SetMargins(20, 20, 20);
    $pdf->AddPage();

    $pdf->SetTextColor(...$themeColors["header_text_color"]);
    $pdf->SetFont("Arial", "B", 24);
    $pdf->Cell(0, 15, "Document Analysis Summary", 0, 1, "C");
    $pdf->SetFont("Arial", "B", 16);
    $pdf->Cell(0, 10, $metaData["title"] ?? "Untitled Document", 0, 1, "C");
    $pdf->Ln(10);

    // Process summaries
    foreach ($pageSummaries as $pageNumber => $summary) {
      $pdf->SetFillColor(...$themeColors["header_bg"]);
      $pdf->SetTextColor(...$themeColors["header_text_color"]);
      $pdf->SetFont("Arial", "B", 14);
      $pdf->Cell(0, 10, "Page $pageNumber Summary", 1, 1, "L", true);
      $pdf->SetTextColor(...$themeColors["text_color"]);

      // Clean and format the text
      $cleanedText = $this->cleanText($summary);
      $this->writeFormattedText($pdf, $cleanedText, $themeColors);
      $pdf->Ln(5);
    }

    // Footer
    $app_name = env("APP_NAME");
    $footerY = $pdf->GetPageHeight() - 30;
    $pdf->SetY($footerY);
    $pdf->SetTextColor(...$themeColors["text_color"]);
    $pdf->SetFont("Arial", "B", 12);
    $pdf->Cell(
      0,
      10,
      "Generated by $app_name on " . Carbon::now()->format('F j, Y \a\t g:i A'),
      0,
      1,
      "C"
    );
    $pdf->SetFont("Arial", "I", 10);
    $pdf->Cell(0, 10, "Page " . $pdf->PageNo(), 0, 0, "C");

    $pdf->Output(
      storage_path(
        "app/summaries/" . pathinfo($filename, PATHINFO_FILENAME) . ".pdf"
      ),
      "F"
    );
    return $filename;
  }
}
