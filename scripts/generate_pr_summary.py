#!/usr/bin/env python3
"""
Generate a Pull Request summary from a git diff using an external AI API.

Usage:
    python generate_pr_summary.py <diff_file> [current_body_file]

Environment variables:
    AI_API_KEY  - API key for the AI service (required)
    AI_API_URL  - Base URL for the AI API endpoint (required)
    AI_MODEL    - Model identifier (default: gemini-3-flash-preview:cloud)

The script reads a git diff and an optional current PR body, then sends them
to the configured AI API to generate a structured PR description.
"""

import os
import sys

import requests


def generate_summary(diff_text: str, current_body: str, api_key: str, api_url: str, model: str) -> str:
    """Send the diff to the AI API and return the generated summary."""

    prompt = f"""You are a senior software engineer reviewing a pull request.
Please analyze the following git diff and generate a structured Pull Request description.

Current PR Body (preserve any existing links or references):
{current_body}

Git Diff:
{diff_text}

Generate a Pull Request description using this exact format:

## What does this change?
[Provide a clear explanation of what changed and why. Focus on the problem being solved.]

## How to test
[Provide step-by-step testing instructions. Mention specific files or functionality to verify.]

## Potential Risks & Review Items
[Identify side effects, performance concerns, security considerations, or areas needing careful review.]

## Is this PR safe for automatic approval?
[Yes/No with brief justification.]

## Images
<!-- If applicable, describe visual changes -->

## Related Tasks / PRs
<!-- Links to related tasks or pull requests -->

## Checklist
- [ ] Unit tests provided?
- [ ] All tests passing?
- [ ] Tested in staging?
- [ ] Task link provided?

Important instructions:
- Preserve any existing links, task references, or images from the Current PR Body.
- Focus on the diff content for the "What does this change?" and "Potential Risks" sections.
- Keep other sections as placeholders if no specific information is available.
- Return ONLY the markdown content.
"""

    headers = {
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json",
    }

    data = {
        "model": model,
        "prompt": prompt,
        "stream": False,
    }

    try:
        response = requests.post(api_url, headers=headers, json=data, timeout=120)
        response.raise_for_status()
        result = response.json()
        return result.get("response", "Could not generate summary.")
    except requests.exceptions.Timeout:
        return "Error: AI API request timed out after 120 seconds."
    except requests.exceptions.RequestException as e:
        return f"Error calling AI API: {e}"


def main() -> int:
    if len(sys.argv) < 2:
        print("Usage: python generate_pr_summary.py <diff_file> [current_body_file]")
        return 1

    diff_file = sys.argv[1]
    current_body_file = sys.argv[2] if len(sys.argv) > 2 else None

    api_key = os.getenv("AI_API_KEY")
    api_url = os.getenv("AI_API_URL")
    model = os.getenv("AI_MODEL", "gemini-3-flash-preview:cloud")

    if not api_key:
        print("Error: AI_API_KEY environment variable not set.")
        return 1

    if not api_url:
        print("Error: AI_API_URL environment variable not set.")
        return 1

    if not os.path.exists(diff_file):
        print(f"Error: Diff file '{diff_file}' not found.")
        return 1

    with open(diff_file, "r", encoding="utf-8") as f:
        diff_text = f.read()

    # Truncate large diffs to avoid API token limits
    max_diff_size = 50000
    if len(diff_text) > max_diff_size:
        diff_text = diff_text[:max_diff_size] + "\n\n... (diff truncated for size) ..."

    current_body = ""
    if current_body_file and os.path.exists(current_body_file):
        with open(current_body_file, "r", encoding="utf-8") as f:
            current_body = f.read()

    summary = generate_summary(diff_text, current_body, api_key, api_url, model)
    print(summary)
    return 0


if __name__ == "__main__":
    sys.exit(main())
