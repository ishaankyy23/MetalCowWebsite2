import os
import re
import sys


ROOT = os.path.abspath(os.path.join(os.getcwd()))
WWW = os.path.join(ROOT, 'www')


def collect_html_files(root):
    files = set()
    for dirpath, _, filenames in os.walk(root):
        for f in filenames:
            if f.lower().endswith('.html'):
                full = os.path.join(dirpath, f)
                rel = os.path.relpath(full, root).replace('\\', '/')
                files.add(rel)
    return files


HREF_RE = re.compile(r'<a[^>]+href\s*=\s*["\']([^"\'#?]+)(?:["\']|$)', re.IGNORECASE)


def normalize_target(src_rel, href):
    if href.startswith('/'):
        candidate = href.lstrip('/')
        return candidate
    src_dir = os.path.dirname(src_rel)
    joined = os.path.normpath(os.path.join(src_dir, href)).replace('\\', '/')
    if joined.startswith('./'):
        joined = joined[2:]
    return joined


def resolve_target(norm, html_set):
    candidates = [norm]
    if not os.path.splitext(norm)[1]:
        candidates.append(norm + '.html')
    candidates.append(norm.rstrip('/') + '/index.html')
    base = os.path.basename(norm)
    if base and base + '.html' not in candidates:
        candidates.append(base + '.html')
    for c in candidates:
        if c in html_set:
            return c, candidates
    return None, candidates


def scan():
    if not os.path.isdir(WWW):
        print('www/ directory not found at', WWW)
        sys.exit(2)
    html_files = collect_html_files(WWW)
    total_links = 0
    broken = []
    external = []

    for rel in sorted(html_files):
        path = os.path.join(WWW, rel)
        try:
            with open(path, 'r', encoding='utf-8', errors='ignore') as fh:
                data = fh.read()
        except Exception as e:
            print('Failed to read', rel, e)
            continue
        for m in HREF_RE.finditer(data):
            href = m.group(1).strip()
            total_links += 1
            if not href or href.startswith('#'):
                continue
            if href.startswith(('http://', 'https://', 'mailto:', 'tel:', 'javascript:')):
                external.append((rel, href))
                continue
            norm = normalize_target(rel, href)
            found, tried = resolve_target(norm, html_files)
            if not found:
                broken.append((rel, href, norm, tried))

    print('Scanned HTML files:', len(html_files))
    print('Total links found:', total_links)
    print('External or skipped links:', len(external))
    print('Broken internal links:', len(broken))
    if broken:
        print('\nBroken links detail:')
        for src, href, norm, tried in broken:
            print('-', src, '->', href)
            print('   normalized:', norm)
            print('   tried:', ', '.join(tried))

    if external:
        print('\nSample external links:')
        for src, href in external[:30]:
            print('-', src, '->', href)

    if broken:
        return 1
    return 0


if __name__ == "__main__":
    rc = scan()
    sys.exit(rc)
