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

## Manual Installation
1. Clone or download this repository:
    ```bash
	git clone https://github.com/youngkmd/freedom.git 
2. Move the files to your web server's document root (e.g., /var/www/html or C:\xampp\htdocs).
3. Install dependencies:
```bash composer install```
4. Access the file manager via your browser (e.g., http://localhost/freedom/index.php).

## Usage
1. Open your browser and navigate to the script's URL (e.g., http://localhost/freedom/index.php).
2. Use the interface to:
3. Browse files and folders.
4. Upload files via the "Upload" button.
5. Edit text files by clicking the edit icon.
6. Import files from URLs using the "Import" feature.
7. Extract ZIP files with the unzip icon.
8. Manage files/folders with rename, delete, copy, cut, and paste actions


## Security Notes
- The script restricts navigation to the defined ROOT_DIR to prevent unauthorized access.
- Ensure proper file permissions (e.g., chmod 775 and chown www-data:www-data on Linux) for the target directory.
- For production use, consider adding authentication and CSRF protection.

## Troubleshooting
- If you encounter issues, enable PHP error reporting by adding this to index.php:

```bash ini_set('display_errors', 1); ```
```bash error_reporting(E_ALL); ```

- Check server logs for detailed error messages.


## Contributing
- Feel free to fork this repository, submit issues, or create pull requests to improve the project!

## License
- This project is licensed under the MIT License. See the  file for details.

## Credits
- Developed by Youngkmd.
