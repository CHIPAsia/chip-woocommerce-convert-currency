#!/usr/bin/env python3
"""
Generate WordPress plugin changelog from git commits using an external AI API.

Usage:
    python generate_release_notes.py <commits_file> <tag>

Environment variables:
    AI_API_KEY  - API key for the AI service (required)
    AI_API_URL  - Base URL for the AI API endpoint (required)
    AI_MODEL    - Model identifier (default: gemini-3-flash-preview:cloud)

Output format follows WordPress plugin changelog conventions:
    = X.Y.Z YYYY-MM-DD =
    * Added - ...
    * Fixed - ...
    * Changed - ...
"""

import os
import sys

import requests


def generate_changelog(commits_text: str, tag: str, api_key: str, api_url: str, model: str) -> str:
    """Send commits to AI API and return WordPress-style changelog."""

    prompt = f"""You are a technical writer preparing changelog entries for a WordPress plugin.

Version: {tag}

Git diff since previous release (showing actual code changes):
{commits_text}

Generate a WordPress plugin changelog entry following this EXACT format:

= VERSION DATE =
* Added - [new features, user-facing additions]
* Fixed - [bug fixes]
* Changed - [improvements, refactors, dependency updates]
* Removed - [only if something was actually removed]

Rules:
- Use "Added" for new features, "Fixed" for bug fixes, "Changed" for improvements.
- Write in plain language that merchants (non-engineers) can understand.
- Group related changes under single bullets.
- Focus on actual code changes in the diff, not just commit messages.
- Skip internal-only refactors that don't affect users.
- Each bullet must start with "* Added -", "* Fixed -", "* Changed -", or "* Removed -".
- The first line must be "= {tag} <today's date> =" in YYYY-MM-DD format.
- Return ONLY the changelog content, no markdown code blocks, no extra text.
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
        return result.get("response", "Could not generate changelog.")
    except requests.exceptions.Timeout:
        return "Error: AI API request timed out after 120 seconds."
    except requests.exceptions.RequestException as e:
        return f"Error calling AI API: {e}"


def main() -> int:
    if len(sys.argv) < 3:
        print("Usage: python generate_release_notes.py <commits_file> <tag>")
        return 1

    commits_file = sys.argv[1]
    tag = sys.argv[2]

    api_key = os.getenv("AI_API_KEY")
    api_url = os.getenv("AI_API_URL")
    model = os.getenv("AI_MODEL", "gemini-3-flash-preview:cloud")

    if not api_key:
        print("Error: AI_API_KEY environment variable not set.")
        return 1

    if not api_url:
        print("Error: AI_API_URL environment variable not set.")
        return 1

    if not os.path.exists(commits_file):
        print(f"Error: Commits file '{commits_file}' not found.")
        return 1

    with open(commits_file, "r", encoding="utf-8") as f:
        commits_text = f.read()

    # Truncate large diffs to avoid API token limits
    max_size = 50000
    if len(commits_text) > max_size:
        commits_text = commits_text[:max_size] + "\n\n... (commits truncated for size) ..."

    changelog = generate_changelog(commits_text, tag, api_key, api_url, model)
    print(changelog)
    return 0


if __name__ == "__main__":
    sys.exit(main())
