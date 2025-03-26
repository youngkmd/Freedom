# Freedom Manager

A simple and lightweight PHP-based file manager that allows users to browse, upload, download, edit, and manage files and folders on a web server.

## Features
- Browse directories and files with a clean, responsive UI.
- Upload multiple files at once.
- Download files with a single click.
- Edit text-based files (e.g., `.txt`, `.php`, `.html`) directly in the browser.
- Import files from external URLs.
- Extract ZIP archives.
- Create, rename, and delete files/folders.
- Copy, cut, and paste functionality using a clipboard.
- Security: Restricts navigation to the server's document root.

## Requirements
- PHP 7.4 or higher
- Web server (e.g., Apache, Nginx)
- PHP extensions:
  - `curl` (for importing files from URLs)
  - `zip` (for ZIP extraction)
  - `fileinfo` (for file type detection)
- Write permissions on the target directory

## Installation

### Using Composer (Recommended)
1. Add the package to your project:
   ```bash
   composer require youngkmd/freedom
