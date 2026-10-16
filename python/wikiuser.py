#!/usr/bin/env python3
"""
wikiuser.py: everything about a Wikimedia editor, from the Action API.

    python3 wikiuser.py                    (asks for usernames, one after another)
    python3 wikiuser.py Punith.nyk
    python3 wikiuser.py TheSandDoctor --wiki enwiki
    python3 wikiuser.py Admin --api http://localhost:8080/api.php     (your own wiki)

It shows:
  1. Their global profile: total edits, when they joined, how many wikis
  2. Where they edit: a bar chart of their busiest wikis
  3. Their latest edits on one wiki, with how much text they added or removed
  4. Their habits: busiest hours, busiest weekdays, favourite pages
  5. Saves everything as JSON in user_<name>.json
"""

import argparse
import json
import sys
from collections import Counter
from datetime import datetime

import requests

META_API = "https://meta.wikimedia.org/w/api.php"
HEADERS = {"User-Agent": "AloysiusWorkshop/1.0 (St Aloysius MediaWiki workshop)"}

# Colours for the terminal (switched off when output is not a terminal)
USE_COLOUR = sys.stdout.isatty()
def colour(text, code):
    return f"\033[{code}m{text}\033[0m" if USE_COLOUR else str(text)
def bold(t): return colour(t, "1")
def green(t): return colour(t, "32")
def red(t): return colour(t, "31")
def grey(t): return colour(t, "90")
def orange(t): return colour(t, "38;5;208")


def ask_api(api_url, params):
    """Send one question to a MediaWiki API and return the answer as a dict."""
    params = {**params, "format": "json", "formatversion": 2}
    answer = requests.get(api_url, params=params, headers=HEADERS, timeout=20)
    answer.raise_for_status()
    return answer.json()


def bar(value, biggest, width=30):
    """A text bar chart: ██████"""
    filled = round(width * value / biggest) if biggest else 0
    return orange("█" * filled) + grey("·" * (width - filled))


def global_profile(user):
    """1 + 2: the user's account on every Wikimedia wiki (only on Wikimedia)."""
    data = ask_api(META_API, {
        "action": "query",
        "meta": "globaluserinfo",
        "guiuser": user,
        "guiprop": "editcount|merged",
    })
    info = data["query"]["globaluserinfo"]
    if "missing" in info:
        return None

    print(bold(f"\n🌍 {info['name']} across Wikimedia"))
    print(f"   Joined:      {info['registration'][:10]}")
    print(f"   Total edits: {bold(info['editcount'])}")
    print(f"   Accounts on: {len(info['merged'])} wikis")

    wikis = sorted(info["merged"], key=lambda w: w["editcount"], reverse=True)
    active = [w for w in wikis if w["editcount"] > 0][:8]
    if active:
        print(bold("\n📊 Where they edit"))
        biggest = active[0]["editcount"]
        for w in active:
            print(f"   {w['wiki']:<18} {bar(w['editcount'], biggest)} {w['editcount']}")
    return info


def local_profile(api_url, user):
    """The user's account on one wiki: edit count and user groups."""
    data = ask_api(api_url, {
        "action": "query",
        "list": "users",
        "ususers": user,
        "usprop": "editcount|registration|groups",
    })
    return data["query"]["users"][0]


def contributions(api_url, user, limit=500):
    """3: the user's latest edits on one wiki (up to 500, newest first)."""
    data = ask_api(api_url, {
        "action": "query",
        "list": "usercontribs",
        "ucuser": user,
        "uclimit": limit,
        "ucprop": "title|timestamp|comment|sizediff|flags|ids",
    })
    return data["query"]["usercontribs"]


def show_latest(edits, count=10):
    print(bold(f"\n✏️  Latest {min(count, len(edits))} edits"))
    for e in edits[:count]:
        size = e.get("sizediff", 0)
        size_text = green(f"+{size}") if size >= 0 else red(str(size))
        new = orange(" NEW") if e.get("new") else ""
        when = e["timestamp"][:16].replace("T", " ")
        comment = (e.get("comment") or "")[:45]
        print(f"   {grey(when)}  {size_text:>16}{new}  {e['title'][:40]}  {grey(comment)}")


def show_habits(edits):
    """4: when and where this person edits, worked out from their edits."""
    if not edits:
        return {}
    times = [datetime.strptime(e["timestamp"], "%Y-%m-%dT%H:%M:%SZ") for e in edits]
    hours = Counter(t.hour for t in times)
    days = Counter(t.strftime("%a") for t in times)
    pages = Counter(e["title"] for e in edits)
    added = sum(e.get("sizediff", 0) for e in edits if e.get("sizediff", 0) > 0)
    removed = -sum(e.get("sizediff", 0) for e in edits if e.get("sizediff", 0) < 0)

    print(bold(f"\n🕒 Habits (from their last {len(edits)} edits)"))
    print(f"   Between {times[-1]:%d %b %Y} and {times[0]:%d %b %Y}")
    print(f"   Text added: {green('+' + str(added))} bytes   removed: {red('-' + str(removed)) if removed else '0'} bytes")
    print(f"   New pages created: {sum(1 for e in edits if e.get('new'))}")

    print(bold("\n   Busiest hours (UTC)"))
    biggest = max(hours.values())
    for h in range(24):
        if hours[h]:
            print(f"   {h:02d}:00  {bar(hours[h], biggest, 25)} {hours[h]}")

    print(bold("\n   Busiest days"))
    biggest = max(days.values())
    for d in ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"]:
        print(f"   {d}    {bar(days[d], biggest, 25)} {days[d]}")

    print(bold("\n   Favourite pages"))
    for title, n in pages.most_common(5):
        print(f"   {n:>4} × {title}")

    return {
        "hours_utc": dict(sorted(hours.items())),
        "weekdays": dict(days),
        "favourite_pages": pages.most_common(10),
        "bytes_added": added,
        "bytes_removed": removed,
    }


def main():
    parser = argparse.ArgumentParser(description="Everything about a Wikimedia editor.")
    parser.add_argument("user", nargs="?", help="Username, for example Punith.nyk")
    parser.add_argument("--wiki", help="Which wiki to look at, for example enwiki or knwiki. "
                                       "Default: the wiki where they edit most")
    parser.add_argument("--api", help="Use any MediaWiki instead, for example http://localhost:8080/api.php")
    parser.add_argument("--json", action="store_true", help="Also print the raw JSON answers")
    args = parser.parse_args()

    if args.user:
        report_for(args.user, args.wiki, args.api, args.json)
        return

    # No username given: keep asking, live, until an empty line
    print(bold("🔎 Wikimedia editor explorer") + grey("   (press Enter on an empty line to quit)"))
    while True:
        user = input(bold("\nUsername: ")).strip()
        if not user:
            break
        wiki = None
        if not args.api:
            wiki = input(grey("Which wiki? (e.g. enwiki, knwiki, commonswiki; Enter = their busiest): ")).strip() or None
        try:
            report_for(user, wiki or args.wiki, args.api, args.json)
        except requests.exceptions.RequestException as error:
            print(red(f"Could not get an answer: {error}"))


def report_for(user, wiki, api, show_json=False):
    """Everything about one user, printed to the terminal and saved as JSON."""
    report = {"user": user}

    if api:
        # Your own wiki: no global accounts there, just this one wiki
        api_url, wiki_name = api, api
    else:
        info = global_profile(user)
        if info is None:
            print(red(f"No Wikimedia user called {user}."))
            return
        report["global"] = info
        wikis = {w["wiki"]: w for w in info["merged"]}
        if wiki:
            if wiki not in wikis:
                print(red(f"{user} has no account on {wiki}."))
                return
            chosen = wikis[wiki]
        else:
            chosen = max(info["merged"], key=lambda w: w["editcount"])
        wiki_name = chosen["wiki"]
        api_url = chosen["url"] + "/w/api.php"

    local = local_profile(api_url, user)
    if "missing" in local:
        print(red(f"No user called {user} on {wiki_name}."))
        return
    report["wiki"] = wiki_name
    report["account"] = local
    groups = [g for g in local.get("groups", []) if g not in ("*", "user", "autoconfirmed")]
    print(bold(f"\n🏠 On {wiki_name}"))
    print(f"   Edits: {bold(local.get('editcount', 0))}   Joined: {(local.get('registration') or '?')[:10]}"
          f"   Special rights: {', '.join(groups) or 'none'}")

    edits = contributions(api_url, user)
    report["edits"] = edits
    show_latest(edits)
    report["habits"] = show_habits(edits)

    filename = f"user_{user.replace(' ', '_').replace('/', '_')}.json"
    with open(filename, "w", encoding="utf-8") as f:
        json.dump(report, f, indent=2, ensure_ascii=False)
    print(bold(f"\n💾 Saved everything to {filename}"))

    if show_json:
        print(json.dumps(report, indent=2, ensure_ascii=False))


if __name__ == "__main__":
    try:
        main()
    except requests.exceptions.ConnectionError:
        print(red("\nCould not reach the wiki. Is the address right? For your own wiki, run: bash start.sh"))
    except requests.exceptions.RequestException as error:
        print(red(f"\nThe wiki answered with an error: {error}"))
    except KeyboardInterrupt:
        print()
