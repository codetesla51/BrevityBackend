<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PDFConfingController extends Controller
{
  public static function getSummaryTypes()
  {
    return [
      "detailed" => [
        "prompt" => "Analyze the following text comprehensively. Your response MUST include ALL of these sections:
        
1. **Executive Summary** (REQUIRED):
   - Write exactly 3 sentences summarizing the core message.
   - Include the main purpose/objective.
   - State the primary conclusion or outcome.

2. **Key Themes** (REQUIRED):
   - List and explain 3-5 major themes.
   - Provide specific evidence/quotes for each theme.
   - Explain how themes interconnect.

3. **Critical Analysis** (REQUIRED):
   - Break down main arguments systematically.
   - Evaluate the quality of evidence presented.
   - Identify any gaps or assumptions.
   - Assess methodology if applicable.

4. **Notable Quotes** (REQUIRED):
   - Extract 3-4 most significant quotes.
   - Explain context and significance of each.
   - Connect quotes to key themes.

5. **Implications** (REQUIRED):
   - List immediate implications.
   - Discuss long-term impact.
   - Identify affected stakeholders.
   - Suggest practical applications.

6. **Conclusions** (REQUIRED):
   - Synthesize key findings.
   - Prioritize main takeaways.
   - Provide recommendations if applicable.

IMPORTANT REQUIREMENTS:
- Every section must be clearly labeled.
- Include specific examples/evidence for all claims.
- Maintain logical flow between sections.
- Use clear topic sentences for each paragraph.
- Ensure all analyses are supported by text evidence.

DO NOT:
- Skip any required section.
- Make unsupported claims.
- Include personal opinions unless specifically analyzing bias.
- Exceed scope of original text.",
      ],
      "short" => [
        "prompt" => "Craft a concise yet comprehensive summary that captures all key points noted in the text. Follow these instructions:

1. **Core Message** (REQUIRED):
   - Summarize the primary purpose and outcome in up to 1-2 sentences.
   - Use clear and precise language to articulate the main point.

2. **Key Points** (REQUIRED):
   - List all notable points or themes from the text.
   - Use bullet points for clarity.
   - Provide brief explanations for each point (1-2 sentences per point).
   - Ensure the points are logically ordered.

3. **Key Insight** (REQUIRED):
   - Highlight the single most important takeaway.
   - Back it up with specific evidence from the text.
   - Explain why this insight is significant.


CRUCIAL REQUIREMENTS:
- Keep the summary concise and well-structured.
- Ensure every statement is based on the txt
- Use bullet points to enhance readability.
- Maintain focus on practical application.

DO NOT:
- Exclude any critical points in the text.
- Use vague or overly abstract language.
- Add unnecessary context or exceed a 200-word limit.",
      ],
      "structured" => [
        "prompt" => "Create a thorough and well-structured analysis. Follow these precise instructions:

1. **Context** (REQUIRED):
   - Provide 2-3 sentences summarizing the background and importance of the topic.
   - Clearly state the purpose or objective of the content.
   - Specify the target audience and their relevance to the topic.

2. **Main Arguments** (REQUIRED):
   - Identify and list exactly 3-4 key arguments.
   - Provide evidence or examples to support each argument.
   - Critically assess the strength and validity of each argument.
   - Show logical connections and relationships between arguments.

3. **Supporting Data** (REQUIRED):
   - Present relevant data, statistics, or metrics.
   - Use specific examples and cite case studies, if available.
   - Ensure the accuracy of all data used.
   - Highlight how the data supports the arguments.

4. **Key Insights** (REQUIRED):
   - Extract 3 practical, real-world insights from the content.
   - Explain the broader business, societal, or real-world implications.
   - Discuss the potential for these insights to be implemented effectively.

CRUCIAL REQUIREMENTS:
- Use clear, organized bullet points for each section.
- Provide concrete metrics and specific evidence wherever possible.
- Maintain consistent, structured formatting.
- Focus on actionable and practical outcomes.

DO NOT:
- Use vague or overly general recommendations.
- Omit critical data or supporting evidence.
- Leave any steps or actions abstract or ambiguous.
- Exceed the original scope of the content.",
      ],
      "dummy" => [
        "prompt" => "Simplify and explain the content in the easiest way possible by following these precise instructions:

1. **Use Everyday Examples** (REQUIRED):
   - Break down complex ideas into everyday, relatable examples.
   - Connect explanations to common, real-life scenarios that are easy to imagine.
   - Keep the language friendly, straightforward, and free of technical jargon.

2. **Step-by-Step Breakdown** (REQUIRED):
   - Divide complicated concepts into smaller, bite-sized steps.
   - Use plain language to explain each step clearly.
   - Add simple analogies to help clarify tricky parts.

3. **Engaging Analogies** (REQUIRED):
   - Include analogies that make sense to a younger audience (e.g., a 12-year-old).
   - Relate abstract ideas to familiar activities or objects (e.g., games, food, daily routines).
   - Keep the tone light, conversational, and engaging.

4. **Text-Based References** (REQUIRED):
   - Always refer back to the original text for evidence or context.
   - Highlight and quote any hard-to-understand parts directly from the text.
   - Explain challenging or dense sections using clear, everyday language.

CRUCIAL REQUIREMENTS:
- Ensure explanations are relatable, clear, and rooted in the source text.
- Use clear, everyday terms to replace any technical language.
- Write as though you are explaining to someone with no background in the topic.

DO NOT:
- Go beyond the content of the source text.
- Skip referencing hard parts or avoid quoting directly from the text.
- Use complicated words, technical jargon, or vague analogies.
- Oversimplify to the point of losing key meaning or depth.
- Overshadow or distort the core message for the sake of creativity.
- Stray from the text’s purpose or omit key content.",
      ],
      "extensive_researcher" => [
        "prompt" => "Conduct a thorough academic analysis with a clear, structured approach. Follow these exact instructions:

1. **Abstract** (REQUIRED):
   - Summarize key findings in 150 words or fewer.
   - Include the core argument and primary conclusions concisely.

2. **Introduction** (REQUIRED):
   - Set the context, objectives, and significance of the work.
   - Define the main research questions and explain their relevance to the field.

3. **Methodology** (REQUIRED):
   - Describe the approach and framework guiding the analysis.
   - Highlight any tools, techniques, or methods employed in the research.

4. **Results Analysis** (REQUIRED):
   - Provide a detailed breakdown of major findings.
   - Evaluate how the results align with initial goals or hypotheses.

5. **Discussion** (REQUIRED):
   - Critically assess the implications of the findings.
   - Address limitations, biases, or gaps identified during the research process.

6. **Future Directions** (REQUIRED):
   - Suggest potential areas for further research.
   - Explain how these inquiries could contribute to advancing the field.

7. **Key References** (REQUIRED):
   - Cite relevant academic works and supporting materials.
   - Connect findings to prior research to strengthen the analysis.

CRUCIAL ELEMENTS:
- Maintain academic rigor with clear and structured arguments.
- Ensure a balanced and objective evaluation of findings.

DO NOT:
- Skip required sections or fail to address key points.
- Overuse technical jargon that distracts from the main arguments.
- Exclude citations or references for any claims.
- Exclude or misrepresent content from the text.
- Use vague or unsupported claims.",
      ],
      "creative" => [
        "prompt" => "Transform the content into a compelling narrative. Follow these exact guidelines:

1. **Engaging Narrative** (REQUIRED):
   - Present the content as an intriguing story that captivates readers.
   - Ensure a seamless balance between creativity and factual accuracy.

2. **Metaphors and Analogies** (REQUIRED):
   - Use vivid and relatable metaphors or analogies to simplify complex ideas.
   - Make explanations more imaginative while keeping them understandable.

3. **Character Perspectives** (REQUIRED):
   - Introduce characters or viewpoints to add depth to the narrative.
   - Use dialogue or internal thoughts to create an engaging experience.

4. **Descriptive Scenarios** (REQUIRED):
   - Craft detailed scenes or settings to bring key points to life.
   - Use sensory details (sights, sounds, emotions) to immerse the reader.

5. **Core Message** (REQUIRED):
   - Preserve and highlight the main message throughout the story.
   - Emphasize clarity and memorability in the narrative’s conclusion.

CRUCIAL ELEMENTS:
- Creativity should enhance, not overshadow, the content’s core message.
- Use accessible language to make the story engaging for all readers.

DO NOT:
- Sacrifice the main message for the sake of creativity.
- Deviate significantly from the content’s original intent or focus.
- Overcomplicate with excessive details or unrelated narratives.",
      ],
      "persuasive" => [
        "prompt" => "Create a compelling argument using these structured steps:

1. **Opening Hook** (REQUIRED):
   - Begin with an attention-grabbing statement that introduces the topic.
   - Ensure the hook clearly connects to the content.

2. **Context** (REQUIRED):
   - Frame the importance of the issue or topic.
   - Provide context that highlights the significance.

3. **Evidence** (REQUIRED):
   - Present the strongest supporting points and evidence.
   - Use specific examples to bolster your argument.

4. **Counter-Arguments** (REQUIRED):
   - Acknowledge potential objections or counter-arguments.
   - Address them logically and respectfully.

5. **Call to Action** (REQUIRED):
   - Conclude with a motivating and clear call to action.
   - Ensure the call to action is direct and practical.

IMPORTANT REQUIREMENTS:
- Maintain clarity and focus.
- Avoid overly aggressive tones.

DO NOT:
- Make unsupported claims.
- Use a tone that may alienate the audience.",
      ],
      "listicle" => [
        "prompt" => "Transform the content into a listicle. Ensure clarity and engagement:

1. **Compelling Headline** (REQUIRED):
   - Create an engaging headline that draws attention.
   - Focus on the key benefit or takeaway.

2. **Clear Points** (REQUIRED):
   - Break the content into 5-10 distinct points.
   - Each point should be clear, actionable, and memorable.

3. **Brief Explanation** (REQUIRED):
   - Add a concise explanation for each point.
   - Ensure each point is meaningful and relevant.

4. **Examples** (REQUIRED):
   - Include relevant examples to support each point.
   - Make each point practical and actionable.

5. **Practical Conclusion** (REQUIRED):
   - End with a conclusion that reinforces the main message.
   - Provide a takeaway or next step for the reader.

IMPORTANT REQUIREMENTS:
- Keep each point clear and concise.
- Focus on actionable takeaways.

DO NOT:
- Include irrelevant points.
- Overcomplicate the explanations.
- Include irrelevant or unsupported ideas.
- Omit critical insights from the text.",
      ],
      "question_answer" => [
        "prompt" => "Create an insightful Q&A format based on the content. Follow these steps:

1. **Fundamental Questions** (REQUIRED):
   - Start with simple, fundamental questions that establish the context.
   - Ensure these questions are relevant to the content.

2. **Complex Inquiries** (REQUIRED):
   - Move to more complex questions that address deeper aspects.
   - Ensure these questions challenge the reader’s understanding.

3. **Practical Applications** (REQUIRED):
   - Include questions that address how the content can be applied in real-life scenarios.
   - Make sure these questions are directly related to practical use cases.

4. **Misconceptions** (REQUIRED):
   - Address common misconceptions or misunderstandings about the topic.
   - Clarify these misconceptions with evidence from the content.

5. **Forward-Looking Questions** (REQUIRED):
   - Conclude with questions that provoke thought about future developments.
   - Encourage the reader to think about how the topic evolves.

IMPORTANT REQUIREMENTS:
- Keep the Q&A format logical and easy to follow.
- Ensure each answer directly addresses the question.

DO NOT:
- Skip important questions.
- Leave answers unclear or incomplete.",
      ],
      "summary_with_examples" => [
        "prompt" => "Create a summary rich in real-world examples. Follow these requirements:

1. **Main Concept** (REQUIRED):
   - Explain the main concept with a clear real-world example.
   - Make sure the example is relatable and easy to understand.

2. **Key Points** (REQUIRED):
   - Break down the key points and support each with a practical case.
   - Ensure each point is supported with a relevant example.

3. **Applications** (REQUIRED):
   - Show how the concept can be applied in practice.
   - Provide examples of real-world scenarios where it’s used.

4. **Scenarios** (REQUIRED):
   - Include different use cases or scenarios to illustrate the concept.
   - Ensure the scenarios are varied and relevant.

5. **Practical Tips** (REQUIRED):
   - Provide actionable tips based on the concept and examples.
   - Make sure the tips are clear and easy to follow.

IMPORTANT REQUIREMENTS:
- Ensure examples are diverse and relevant.
- Focus on practical applications.

DO NOT:
- Skip any key point.
- Use irrelevant or confusing examples.",
      ],
      "emphasized_takeaways" => [
        "prompt" => "Focus on extracting key insights and emphasizing practical takeaways. Follow these steps:

1. **Core Message** (REQUIRED):
   - Identify the single most important point of the content.
   - Keep it clear and focused.

2. **Critical Insights** (REQUIRED):
   - Highlight 3-5 key insights that offer valuable lessons.
   - Support each with specific evidence from the content.

3. **Practical Applications** (REQUIRED):
   - Explain how to apply these insights in real life.
   - Ensure the applications are actionable and clear.

4. **Action Items** (REQUIRED):
   - Provide specific steps for implementing the insights.
   - Ensure each action is practical and measurable.

5. **Success Metrics** (REQUIRED):
   - Define how to measure the success of the actions.
   - Include concrete metrics to track progress.

IMPORTANT REQUIREMENTS:
- Keep it concise and action-focused.
- Avoid abstract insights.

DO NOT:
- Include unnecessary information.
- Skip actionable steps.",
      ],
      "problem_solution" => [
        "prompt" => "Analyze a problem and provide a thorough solution. Follow these steps:

1. **Problem Context** (REQUIRED):
   - Explain the background and significance of the problem.
   - Make sure the context is clear and relevant.

2. **Key Challenges** (REQUIRED):
   - Break down the main challenges associated with the problem.
   - Be specific in identifying these challenges.

3. **Solution Framework** (REQUIRED):
   - Provide a comprehensive approach to solving the problem.
   - Ensure the solution is logical and actionable.

4. **Implementation Steps** (REQUIRED):
   - Outline clear steps for implementing the solution.
   - Ensure the steps are practical and measurable.

5. **Success Criteria** (REQUIRED):
   - Define how to measure the success of the solution.
   - Provide specific criteria for success.

6. **Risk Mitigation** (REQUIRED):
   - Address potential risks or obstacles in the solution.
   - Provide strategies for overcoming these challenges.

IMPORTANT REQUIREMENTS:
- Focus on clear, actionable solutions.
- Include measurable success criteria.

DO NOT:
- Skip key challenges or steps.
- Leave risks unaddressed.",
      ],
      "opinionated_summary" => [
        "prompt" => "Provide a critical analysis with a balanced perspective. Follow these steps:

1. **Content Overview** (REQUIRED):
   - Provide an objective summary of the content.
   - Be concise and avoid personal opinions.

2. **Strengths** (REQUIRED):
   - Highlight what works well in the content and why.
   - Provide clear examples to support your analysis.

3. **Limitations** (REQUIRED):
   - Identify areas for improvement.
   - Be constructive and provide evidence.

4. **Alternative Views** (REQUIRED):
   - Present different perspectives on the content.
   - Respectfully consider other viewpoints.

5. **Recommendations** (REQUIRED):
   - Offer suggestions for improvement or enhancement.
   - Focus on practical, actionable recommendations.

IMPORTANT REQUIREMENTS:
- Maintain a respectful tone.
- Support all opinions with clear reasoning.

DO NOT:
- Be overly critical.
- Skip any required sections.",
      ],
      "objective_questions" => [
        "prompt" => "Generate multiple-choice questions based on the content. Follow these guidelines:

1. **Core Concepts** (REQUIRED):
   - Create questions that target the key ideas and concepts.
   - Focus on assessing understanding of the content.

2. **Options** (REQUIRED):
   - Provide 4 plausible answer options (A, B, C, D) for each question.
   - Ensure the options are clear and distinct.

3. **Correct Answer** (REQUIRED):
   - Indicate which option is correct for each question.but at the very end of all the questions explain the Answer based on the pdf

4. **Difficulty Levels** (REQUIRED):
   - Include questions of varying difficulty (easy, medium, hard).
   - Ensure the difficulty levels are balanced.

5. **Clarity** (REQUIRED):
 - Ensure the questions and options are clear and unambiguous based on text.
   - Avoid confusing or misleading phrasing.

IMPORTANT REQUIREMENTS:
- Ensure the questions are well-structured.
- Avoid overly tricky or misleading questions.

DO NOT:
- Skip any key concepts.
- Include vague or confusing options.",
      ],
    ];
  }
  public static function getThemes()
  {
    return [
      "default" => [
        "name" => "Default",
        "header_bg" => [250, 250, 250],
        "text_color" => [51, 51, 51],
        "header_text_color" => [33, 33, 33],
        "line_color" => [229, 231, 235],
        "accent_color" => [59, 130, 246],
        "secondary_color" => [241, 245, 249],
      ],

      "dark" => [
        "name" => "Dark",
        "header_bg" => [31, 41, 55],
        "text_color" => [229, 231, 235],
        "header_text_color" => [255, 255, 255],
        "line_color" => [75, 85, 99],
        "accent_color" => [31, 41, 55],
        "secondary_color" => [31, 41, 55],
      ],

      "blue" => [
        "name" => "Blue",
        "header_bg" => [239, 246, 255],
        "text_color" => [30, 58, 138],
        "header_text_color" => [29, 78, 216],
        "line_color" => [191, 219, 254],
        "accent_color" => [59, 130, 246],
        "secondary_color" => [219, 234, 254],
      ],

      "professional" => [
        "name" => "Professional",
        "header_bg" => [248, 250, 252],
        "text_color" => [30, 41, 59],
        "header_text_color" => [15, 23, 42],
        "line_color" => [226, 232, 240],
        "accent_color" => [51, 65, 85],
        "secondary_color" => [241, 245, 249],
      ],

      "modern" => [
        "name" => "Modern",
        "header_bg" => [250, 250, 250],
        "text_color" => [15, 23, 42],
        "header_text_color" => [2, 132, 199],
        "line_color" => [226, 232, 240],
        "accent_color" => [14, 165, 233],
        "secondary_color" => [240, 249, 255],
      ],

      "warm" => [
        "name" => "Warm",
        "header_bg" => [255, 251, 235],
        "text_color" => [120, 53, 15],
        "header_text_color" => [194, 65, 12],
        "line_color" => [254, 243, 199],
        "accent_color" => [234, 88, 12],
        "secondary_color" => [255, 237, 213],
      ],

      "nature" => [
        "name" => "Nature",
        "header_bg" => [240, 253, 244],
        "text_color" => [20, 83, 45],
        "header_text_color" => [22, 101, 52],
        "line_color" => [187, 247, 208],
        "accent_color" => [34, 197, 94],
        "secondary_color" => [220, 252, 231],
      ],

      "elegant" => [
        "name" => "Elegant",
        "header_bg" => [250, 245, 255],
        "text_color" => [88, 28, 135],
        "header_text_color" => [126, 34, 206],
        "line_color" => [233, 213, 255],
        "accent_color" => [147, 51, 234],
        "secondary_color" => [243, 232, 255],
      ],

      "tech" => [
        "name" => "Tech",
        "header_bg" => [240, 253, 250],
        "text_color" => [19, 78, 74],
        "header_text_color" => [13, 148, 136],
        "line_color" => [204, 251, 241],
        "accent_color" => [20, 184, 166],
        "secondary_color" => [153, 246, 228],
      ],

      "minimal" => [
        "name" => "Minimal",
        "header_bg" => [250, 250, 250],
        "text_color" => [23, 23, 23],
        "header_text_color" => [23, 23, 23],
        "line_color" => [229, 229, 229],
        "accent_color" => [64, 64, 64],
        "secondary_color" => [245, 245, 245],
      ],

      // Keeping existing successful themes
      "midnight" => [
        "name" => "Midnight",
        "header_bg" => [17, 24, 39],
        "text_color" => [243, 244, 246],
        "header_text_color" => [255, 255, 255],
        "line_color" => [55, 65, 81],
        "accent_color" => [129, 140, 248],
        "secondary_color" => [30, 41, 59],
      ],

      "corporate" => [
        "name" => "Corporate",
        "header_bg" => [248, 250, 252],
        "text_color" => [30, 41, 59],
        "header_text_color" => [15, 23, 42],
        "line_color" => [226, 232, 240],
        "accent_color" => [51, 65, 85],
        "secondary_color" => [241, 245, 249],
      ],

      "forest" => [
        "name" => "Forest",
        "header_bg" => [240, 253, 244],
        "text_color" => [20, 83, 45],
        "header_text_color" => [22, 101, 52],
        "line_color" => [187, 247, 208],
        "accent_color" => [34, 197, 94],
        "secondary_color" => [220, 252, 231],
      ],

      "ocean" => [
        "name" => "Ocean",
        "header_bg" => [239, 246, 255],
        "text_color" => [30, 58, 138],
        "header_text_color" => [29, 78, 216],
        "line_color" => [191, 219, 254],
        "accent_color" => [59, 130, 246],
        "secondary_color" => [219, 234, 254],
      ],

      "slate" => [
        "name" => "Slate",
        "header_bg" => [248, 250, 252],
        "text_color" => [51, 65, 85],
        "header_text_color" => [30, 41, 59],
        "line_color" => [226, 232, 240],
        "accent_color" => [71, 85, 105],
        "secondary_color" => [241, 245, 249],
      ],

      "violet" => [
        "name" => "Violet",
        "header_bg" => [250, 245, 255],
        "text_color" => [109, 40, 217],
        "header_text_color" => [126, 34, 206],
        "line_color" => [233, 213, 255],
        "accent_color" => [147, 51, 234],
        "secondary_color" => [243, 232, 255],
      ],

      "rose" => [
        "name" => "Rose",
        "header_bg" => [255, 241, 242],
        "text_color" => [190, 18, 60],
        "header_text_color" => [225, 29, 72],
        "line_color" => [254, 205, 211],
        "accent_color" => [244, 63, 94],
        "secondary_color" => [254, 226, 226],
      ],

      "monochrome" => [
        "name" => "Monochrome",
        "header_bg" => [250, 250, 250],
        "text_color" => [23, 23, 23],
        "header_text_color" => [23, 23, 23],
        "line_color" => [229, 229, 229],
        "accent_color" => [64, 64, 64],
        "secondary_color" => [245, 245, 245],
      ],

      "sepia" => [
        "name" => "Sepia",
        "header_bg" => [254, 252, 232],
        "text_color" => [133, 77, 14],
        "header_text_color" => [161, 98, 7],
        "line_color" => [254, 249, 195],
        "accent_color" => [202, 138, 4],
        "secondary_color" => [254, 240, 138],
      ],

      "arctic" => [
        "name" => "Arctic",
        "header_bg" => [245, 250, 255],
        "text_color" => [12, 74, 110],
        "header_text_color" => [3, 105, 161],
        "line_color" => [186, 230, 253],
        "accent_color" => [14, 165, 233],
        "secondary_color" => [224, 242, 254],
      ],
    ];
  }
}
