<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editing <?= htmlspecialchars(basename($file)) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-blue-600 mb-4"><i class="fas fa-edit mr-2"></i>Editing: <?= htmlspecialchars(basename($file)) ?></h2>
            <form method="POST">
                <textarea name="content" rows="20" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono bg-gray-50"><?= $content ?></textarea>
                <div class="mt-4 flex space-x-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i class="fas fa-save mr-2"></i>Save</button>
                    <a href="?dir=<?= urlencode($directory) ?>" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i class="fas fa-times mr-2"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>