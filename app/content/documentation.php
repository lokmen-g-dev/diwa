<?php
session_start();

// Get content of directory
try {
    $rootPath = ROOT_PATH . '/../docs/';
    $path = './';
    if (isset($_GET['path']) && !empty($_GET['path'])) {
        $sanitizedPath = realpath($rootPath . $_GET['path']);
        if (strpos($sanitizedPath, realpath($rootPath)) === 0 && file_exists($sanitizedPath)) {
            $path = $_GET['path'];
        }
    }

    // Ensure last character is a /
    if ($path[strlen($path) - 1] !== '/') {
        $path .= '/';
    }

    $globList = glob($rootPath . $path . '*');
} catch (Exception $ex) {
    error(500, 'Could not get content of directory', $ex);
}
?>

<div class="row">
    <div class="col-lg-3">
        <h1>Documentation</h1>
        <hr/>
        <ul class="list-group">
            <?php
            if ($path !== './') {
                echo '<li class="list-group-item">' . icon('folder-close') . ' <strong><a href="?page=documentation&path=' . urlencode(dirname($path)) . '/">..</a></strong></li>';
            }
            $files = array();
            // Output directories
            foreach ($globList as $item) {
                if (is_dir($item)) {
                    echo '<li class="list-group-item">' . icon('folder-close') . ' <strong><a href="?page=documentation&path=' . urlencode($path . basename($item)) . '/">' . htmlspecialchars(basename($item), ENT_QUOTES, 'UTF-8') . '</a></strong></li>';
                } else {
                    $files[] = $item;
                }
            }

            // Output files
            foreach ($files as $file) {
                echo '<li class="list-group-item">' . icon('file') . ' <strong><a href="?page=documentation&path=' . urlencode($path) .'&file=' . urlencode(basename($file)) . '">' . htmlspecialchars(basename($file), ENT_QUOTES, 'UTF-8') . '</a></strong></li>';
            }
            ?>
        </ul>
    </div>
    <div class="col-lg-9">
        <?php
        if (isset($_GET['file']) && !empty($_GET['file'])) {
            $sanitizedFile = basename($_GET['file']);
            echo '<h1>' . htmlspecialchars($sanitizedFile, ENT_QUOTES, 'UTF-8') . '</h1><hr/>';
            $filePath = $rootPath . $path . $sanitizedFile;
            if (file_exists($filePath)) {
                $fileExtension = strtolower(pathinfo($sanitizedFile, PATHINFO_EXTENSION));
                $validMarkdownExtensions = array('md', 'markdown');
                $validExtensions = array_merge(array('txt', 'text', 'asciidoc', 'adoc'), $validMarkdownExtensions);
                if (in_array($fileExtension, $validExtensions)) {
                    if (filesize($filePath) <= 512 * 1024) {
                        $fileContent = file_get_contents($filePath);
                        // Try to parse markdown files
                        $markdown = false;
                        if (in_array($fileExtension, $validMarkdownExtensions)) {
                            if (class_exists('Parsedown')) {
                                try {
                                    $parsedown = new Parsedown();
                                    echo $parsedown->text($fileContent);
                                    $markdown = true;
                                } catch (Exception $ex) {
                                    // Leave empty
                                }
                            }
                        }
                        // Don't parse markdown
                        if (!$markdown) {
                            echo '<pre>' . htmlspecialchars($fileContent, ENT_QUOTES, 'UTF-8') . '</pre>';
                        }
                    } else {
                        echo '<div class="alert alert-info">The file ' . htmlspecialchars($sanitizedFile, ENT_QUOTES, 'UTF-8') . ' is too big (512KB max).</div>';
                    }
                } else {
                    echo '<div class="alert alert-info">You can only view files with the following file extensions: ' . implode(', ', $validExtensions) . '</div>';
                }
            } else {
                echo '<div class="alert alert-info">The file ' . htmlspecialchars($sanitizedFile, ENT_QUOTES, 'UTF-8') . ' does not exist.</div>';
            }
        }
        ?>
    </div>
</div>
