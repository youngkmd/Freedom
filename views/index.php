<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gray-800 text-white px-6 py-4">
				<div class="flex justify-between items-center">
					<h1 class="text-2xl font-bold"><i class="fas fa-folder-open mr-2"></i>File Manager</h1>
					<a href="?dir=<?= urlencode(ROOT_DIR) ?>" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 rounded-md text-sm"><i class="fas fa-home mr-1"></i>Home</a>
				</div>
				<div class="mt-2 text-gray-300"><?= \App\Utils\FileUtils::createBreadcrumb($directory, ROOT_DIR) ?></div>
			</div>
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <form method="POST" class="flex items-center space-x-2">
                        <input type="text" name="new_folder" placeholder="New folder name" required class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"><i class="fas fa-folder-plus mr-1"></i>Create</button>
                    </form>
                    <form method="POST" enctype="multipart/form-data" class="flex items-center space-x-2">
                        <input type="file" name="files[]" multiple class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md file:mr-2 file:py-1 file:px-3 file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"><i class="fas fa-upload mr-1"></i>Upload</button>
                    </form>
                    <form method="POST" class="flex items-center space-x-2">
                        <input type="text" name="file_url" placeholder="File URL to import" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <button type="submit" class="px-3 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm"><i class="fas fa-cloud-download-alt mr-1"></i>Import</button>
                    </form>
                </div>
                <?php if (isset($_SESSION['clipboard'])): ?>
                    <div class="mt-3">
                        <a href="?paste=1&dir=<?= urlencode($directory) ?>" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                            <i class="fas fa-paste mr-1"></i>Paste <?= $_SESSION['clipboard']['action'] === 'copy' ? '(Copy)' : '(Move)' ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modified</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($items as $item): ?>
                            <?php if ($item === '.' || $item === '..') continue; ?>
                            <?php
                            $path = $directory . '/' . $item;
                            $isDir = is_dir($path);
                            $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas <?= $isDir ? 'fa-folder text-yellow-500' : \App\Utils\FileUtils::getFileIcon($item) ?> mr-3"></i>
                                        <?php if ($isDir): ?>
                                            <a href="?dir=<?= urlencode($path) ?>" class="text-blue-600 hover:text-blue-800 font-medium"><?= htmlspecialchars($item) ?></a>
                                        <?php else: ?>
                                            <span class="text-gray-900"><?= htmlspecialchars($item) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $isDir ? 'Folder' : strtoupper($extension) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono"><?= $isDir ? '-' : \App\Utils\FileUtils::formatFileSize(filesize($path)) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= \App\Utils\FileUtils::getLastModifiedDate($path) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <?php if ($isDir): ?>
                                            <a href="?dir=<?= urlencode($path) ?>" title="Open" class="text-blue-600 hover:text-blue-900"><i class="fas fa-folder-open"></i></a>
                                            <a href="?rename=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Rename" class="text-yellow-600 hover:text-yellow-900"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="?delete=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Delete" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></a>
                                            <a href="?copy=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Copy" class="text-indigo-600 hover:text-indigo-900"><i class="fas fa-copy"></i></a>
                                            <a href="?cut=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Cut" class="text-purple-600 hover:text-purple-900"><i class="fas fa-cut"></i></a>
                                        <?php else: ?>
                                            <a href="?download=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Download" class="text-blue-600 hover:text-blue-900"><i class="fas fa-download"></i></a>
                                            <?php if (in_array($extension, ['txt', 'php', 'html', 'css', 'js', 'json', 'xml', 'md'])): ?>
                                                <a href="?edit=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Edit" class="text-green-600 hover:text-green-900"><i class="fas fa-edit"></i></a>
                                            <?php endif; ?>
                                            <?php if ($extension === 'zip'): ?>
                                                <a href="?unzip=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Unzip" onclick="return confirm('Are you sure?')" class="text-yellow-600 hover:text-yellow-900"><i class="fas fa-file-archive"></i></a>
                                            <?php endif; ?>
                                            <a href="?rename=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Rename" class="text-yellow-600 hover:text-yellow-900"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="?delete=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Delete" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></a>
                                            <a href="?copy=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Copy" class="text-indigo-600 hover:text-indigo-900"><i class="fas fa-copy"></i></a>
                                            <a href="?cut=<?= urlencode($item) ?>&dir=<?= urlencode($directory) ?>" title="Cut" class="text-purple-600 hover:text-purple-900"><i class="fas fa-cut"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 text-center text-sm text-gray-500">
                <a href="https://github.com/youngkmd" target="_blank" class="text-blue-600 hover:text-blue-800"><i class="fab fa-github mr-1"></i>Made with freedom</a>
            </div>
        </div>
    </div>
</body>
</html>