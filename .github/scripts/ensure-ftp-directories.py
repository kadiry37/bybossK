"""Restore missing destination directories before the incremental FTP upload."""

import os
import sys
from ftplib import FTP, error_perm
from pathlib import Path


def main():
    source = Path(sys.argv[1])
    if not source.is_dir():
        raise SystemExit(f"Build directory is missing: {source}")

    with FTP(os.environ["FTP_SERVER"], timeout=60) as ftp:
        ftp.login(os.environ["FTP_USERNAME"], os.environ["FTP_PASSWORD"])
        ftp.cwd(sys.argv[2])
        root = ftp.pwd()
        directories = sorted(
            path.relative_to(source)
            for path in source.rglob("*")
            if path.is_dir() and any(child.is_file() for child in path.iterdir())
        )
        known = {()}
        for directory in directories:
            for depth in range(1, len(directory.parts) + 1):
                parts = directory.parts[:depth]
                if parts in known:
                    continue
                ftp.cwd(root)
                relative = "/".join(parts)
                try:
                    ftp.cwd(relative)
                except error_perm as error:
                    if not str(error).startswith("550"):
                        raise
                    ftp.mkd(relative)
                    ftp.cwd(relative)
                    print(f"Created missing destination directory: {relative}")
                known.add(parts)


if __name__ == "__main__":
    main()
