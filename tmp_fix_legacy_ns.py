#!/usr/bin/env python3
from pathlib import Path

LEGACY = Path(r"e:\mod-health-app\app\Http\Controllers\Legacy")
for path in LEGACY.glob("*.php"):
    text = path.read_text(encoding="utf-8")
    new = text.replace(
        "namespace App\\Http\\Controllers\\Legacy\\Legacy;",
        "namespace App\\Http\\Controllers\\Legacy;",
    )
    if new != text:
        path.write_text(new, encoding="utf-8", newline="\n")
        print("fixed", path.name)
    else:
        # show current namespace line
        for line in text.splitlines()[:5]:
            if line.startswith("namespace"):
                print(path.name, "->", line)
