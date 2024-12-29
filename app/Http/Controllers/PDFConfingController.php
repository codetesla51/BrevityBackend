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
        "prompt" => "Create a focused, precise summary following these EXACT requirements:
        
1. **Core Message** (REQUIRED):
   - Write maximimum of 5 sentences capturing the main point.
   - Include the primary purpose and outcome.
   - Use clear, direct language.

2. **Key Insight** (REQUIRED):
   - Identify THE single most important insight.
   - Support with specific evidence from text.
   - Explain why this insight matters.

3. **Action Item** (REQUIRED):
   - Provide ONE specific, actionable takeaway.
   - Make it measurable and achievable.
   - Include implementation timeframe.

CRUCIAL ELEMENTS:
- Total length must be under 400 words can Exceed of required.
- Every statement must be evidence-based.
- Use active voice.
- Focus on practical application.

DO NOT:
- Include multiple insights or actions.
- Use vague or abstract language.
- Add unnecessary context.
- Exceed specified length.",
      ],
      "structured" => [
        "prompt" => "Create a structured analysis that MUST include:
        
1. **Context** (REQUIRED):
   - Provide essential background in 2-3 sentences.
   - State the purpose/objective clearly.
   - Identify target audience.

2. **Main Arguments** (REQUIRED):
   - List exactly 3-4 key arguments.
   - Support each with specific evidence.
   - Evaluate strength of each argument.
   - Show logical connections.

3. **Supporting Data** (REQUIRED):
   - Present relevant statistics/numbers.
   - Include specific examples.
   - Cite case studies if available.
   - Verify data accuracy.

4. **Key Insights** (REQUIRED):
   - Extract 3 practical insights.
   - Connect to business/real-world impact.
   - Explain implementation potential.

5. **Action Items** (REQUIRED):
   - List 3-5 specific, actionable steps.
   - Include timeline for each.
   - Define success metrics.
   - Address potential obstacles.

CRITICAL REQUIREMENTS:
- Use bullet points for clarity.
- Include specific metrics where possible.
- Maintain consistent formatting.
- Focus on actionable insights.

DO NOT:
- Use vague recommendations.
- Skip quantitative data.
- Leave action items abstract.
- Exceed scope of source material.",
      ],
      "dummy" => [
        "prompt" => "Explain the content in simple terms. Follow these exact instructions:
        
1. **Use Everyday Examples** (REQUIRED):
   - Break down complex concepts with relatable, easy-to-understand examples.
   - Make sure the examples are relevant to common experiences.
   - Avoid jargon and use clear, friendly language.

2. **Step-by-Step Breakdown** (REQUIRED):
   - Simplify complex ideas into smaller, easy-to-digest steps.
   - Use simple analogies to explain each step.
   - Ensure clarity in every step of the explanation.

3. **Engaging Analogies** (REQUIRED):
   - Use relatable analogies to explain difficult concepts.
   - Focus on comparisons that a 12-year-old would easily understand.
   - Keep the tone conversational and approachable.

IMPORTANT REQUIREMENTS:
- Use clear, everyday language.
- Avoid technical jargon.
- Keep explanations relatable and understandable for a younger audience.

DO NOT:
- Use overly technical language.
- Skip any required section.
- Overcomplicate concepts.",
      ],
      "extensive_researcher" => [
        "prompt" => "Conduct a thorough academic analysis with a clear, structured approach. Ensure depth and clarity:

1. **Abstract** (REQUIRED):
   - Provide a concise summary of the key findings (150 words).
   - Include the core argument and major conclusions.

2. **Introduction** (REQUIRED):
   - Establish context, objectives, and significance of the work.
   - Identify the main research questions and their relevance.

3. **Methodology** (REQUIRED):
   - Outline the approach and framework used to conduct the analysis.
   - Discuss any tools or methods that were employed in research.

4. **Results Analysis** (REQUIRED):
   - Provide a detailed breakdown of the main findings.
   - Evaluate how these findings align with the research goals.

5. **Discussion** (REQUIRED):
   - Critically evaluate the implications of the findings.
   - Address limitations, biases, or gaps identified during the research.

6. **Future Directions** (REQUIRED):
   - Suggest potential areas for further research and exploration.
   - Discuss how these future inquiries could enhance the field.

7. **Key References** (REQUIRED):
   - Cite relevant academic works and supporting materials.
   - Provide connections to prior research and findings.

IMPORTANT REQUIREMENTS:
- Maintain academic rigor.
- Avoid overly technical details that may distract from the main arguments.

DO NOT:
- Skip any required section.
- Use overly technical or inaccessible language.
- Leave out key references or evidence.",
      ],
      "creative" => [
        "prompt" => "Transform the content into a compelling narrative. Follow these guidelines to maintain creativity and accuracy:

1. **Engaging Narrative** (REQUIRED):
   - Turn the content into a compelling story that grabs attention.
   - Maintain a balance between creativity and accuracy.

2. **Metaphors and Analogies** (REQUIRED):
   - Use vivid metaphors and analogies to convey complex ideas.
   - Make the content more engaging while keeping the message clear.

3. **Character Perspectives** (REQUIRED):
   - Introduce characters or perspectives that add depth to the story.
   - Use dialogue or internal thoughts to enhance engagement.

4. **Descriptive Scenarios** (REQUIRED):
   - Paint vivid, descriptive scenes that help explain key points.
   - Use the setting and mood to reinforce the message.

5. **Core Message** (REQUIRED):
   - Ensure the main message is preserved throughout the creative transformation.
   - Focus on making the core message memorable and impactful.

IMPORTANT REQUIREMENTS:
- Creativity must not undermine the core message.
- Focus on engagement while staying true to the content’s integrity.

DO NOT:
- Neglect the main message for creativity’s sake.
- Stray too far from the content’s original purpose.",
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
- Overcomplicate the explanations.",
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
   - Indicate which option is correct for each question.

4. **Difficulty Levels** (REQUIRED):
   - Include questions of varying difficulty (easy, medium, hard).
   - Ensure the difficulty levels are balanced.

5. **Clarity** (REQUIRED):
   - Ensure the questions and options are clear and unambiguous.
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
  }
}
