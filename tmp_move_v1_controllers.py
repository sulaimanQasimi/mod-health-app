#!/usr/bin/env python3
"""Move App\\Http\\Controllers\\V1\\* to App\\Http\\Controllers\\* and park conflicts in Legacy."""

from __future__ import annotations

import re
import shutil
from pathlib import Path

ROOT = Path(r"e:\mod-health-app")
CTRL = ROOT / "app" / "Http" / "Controllers"
V1 = CTRL / "V1"
LEGACY = CTRL / "Legacy"

# Top-level PHP files in Controllers that collide with V1 top-level names.
CONFLICTING = {
    "AnesthesiaController.php",
    "AppointmentController.php",
    "HospitalizationController.php",
    "ICUController.php",
    "OperationController.php",
    "OutcomeController.php",
    "PACUController.php",
    "PatientController.php",
    "PhysiotherapyReportController.php",
    "VitalSignController.php",
}

NS_V1 = r"App\Http\Controllers\V1"
NS_ROOT = r"App\Http\Controllers"
NS_LEGACY = r"App\Http\Controllers\Legacy"


def rewrite_file(path: Path, replacements: list[tuple[str, str]]) -> None:
    text = path.read_text(encoding="utf-8")
    original = text
    for old, new in replacements:
        text = text.replace(old, new)
    if text != original:
        path.write_text(text, encoding="utf-8", newline="\n")


def move_conflicts_to_legacy() -> None:
    LEGACY.mkdir(parents=True, exist_ok=True)
    for name in CONFLICTING:
        src = CTRL / name
        if not src.exists():
            print(f"skip missing conflict: {name}")
            continue
        dest = LEGACY / name
        if dest.exists():
            raise SystemExit(f"Legacy already has {name}")
        print(f"legacy: {name}")
        shutil.move(str(src), str(dest))
        # Fix namespace in moved legacy file
        rewrite_file(
            dest,
            [
                (f"namespace {NS_ROOT};", f"namespace {NS_LEGACY};"),
                (f"namespace {NS_ROOT}\\", f"namespace {NS_LEGACY}\\"),
            ],
        )


def move_v1_tree() -> None:
    if not V1.exists():
        raise SystemExit("V1 folder missing")

    # Move everything under V1 into Controllers root
    for item in list(V1.iterdir()):
        dest = CTRL / item.name
        if dest.exists():
            # Empty leftover dirs from earlier attempts
            if dest.is_dir() and not any(dest.rglob("*.php")):
                print(f"remove empty dir: {dest}")
                shutil.rmtree(dest)
            else:
                raise SystemExit(f"Destination exists: {dest}")
        print(f"move: V1/{item.name} -> {item.name}")
        shutil.move(str(item), str(dest))

    # Remove empty V1
    if V1.exists():
        shutil.rmtree(V1)
        print("removed V1/")


def rewrite_namespaces_in_moved() -> None:
    # All PHP under Controllers except Legacy, Auth, Api, and base Controller if needed
    for path in CTRL.rglob("*.php"):
        if "Legacy" in path.parts:
            continue
        if path.name == "Controller.php" and path.parent == CTRL:
            continue
        text = path.read_text(encoding="utf-8")
        if NS_V1 not in text and "Controllers\\V1" not in text:
            # Still may need namespace App\Http\Controllers\V1...
            if "namespace App\\Http\\Controllers\\V1" not in text and "use App\\Http\\Controllers\\V1" not in text:
                continue
        new = text.replace(NS_V1, NS_ROOT)
        # Fix double Controllers\Controllers if any
        new = new.replace("App\\Http\\Controllers\\Controllers\\", "App\\Http\\Controllers\\")
        if new != text:
            path.write_text(new, encoding="utf-8", newline="\n")
            print(f"ns: {path.relative_to(ROOT)}")


def rewrite_project_references() -> None:
    patterns = [
        ROOT / "routes",
        ROOT / "app",
        ROOT / "scripts",
        ROOT / "tests",
    ]
    for base in patterns:
        if not base.exists():
            continue
        for path in base.rglob("*"):
            if path.suffix not in {".php", ".md", ".txt"}:
                continue
            if "Legacy" in path.parts and path.parent == LEGACY:
                # already fixed
                pass
            text = path.read_text(encoding="utf-8")
            if NS_V1 not in text and "Controllers\\V1\\" not in text and "Controllers/V1/" not in text:
                continue
            new = text.replace(NS_V1, NS_ROOT)
            new = new.replace("Controllers\\V1\\", "Controllers\\")
            new = new.replace("Controllers/V1/", "Controllers/")
            if new != text:
                path.write_text(new, encoding="utf-8", newline="\n")
                print(f"ref: {path.relative_to(ROOT)}")


def rewrite_web_legacy_imports() -> None:
    web = ROOT / "routes" / "web.php"
    text = web.read_text(encoding="utf-8")
    for name in [
        "AnesthesiaController",
        "AppointmentController",
        "HospitalizationController",
        "ICUController",
        "OperationController",
        "OutcomeController",
        "PACUController",
        "PatientController",
        "PhysiotherapyReportController",
        "VitalSignController",
    ]:
        text = text.replace(
            f"use App\\Http\\Controllers\\{name};",
            f"use App\\Http\\Controllers\\Legacy\\{name};",
        )
    web.write_text(text, encoding="utf-8", newline="\n")
    print("updated routes/web.php Legacy imports")


def main() -> None:
    move_conflicts_to_legacy()
    move_v1_tree()
    rewrite_namespaces_in_moved()
    rewrite_project_references()
    rewrite_web_legacy_imports()
    print("DONE")


if __name__ == "__main__":
    main()
