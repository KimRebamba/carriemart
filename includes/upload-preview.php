<?php

if (!function_exists('cm_preview_bootstrap')) {
    function cm_preview_bootstrap() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['cm_preview_files'])) {
            $_SESSION['cm_preview_files'] = [];
        }
    }

    function cm_preview_dir($scope) {
        $root = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . DIRECTORY_SEPARATOR . 'carriemart' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tmp_previews' . DIRECTORY_SEPARATOR . $scope;
        if (!is_dir($root)) {
            @mkdir($root, 0777, true);
        }
        return $root;
    }

    function cm_preview_cleanup($scope) {
        cm_preview_bootstrap();
        if (!isset($_SESSION['cm_preview_files'][$scope])) {
            return;
        }
        $entries = $_SESSION['cm_preview_files'][$scope];
        $now = time();
        foreach ($entries as $token => $info) {
            if (!isset($info['created']) || ($now - (int)$info['created']) > 3600) {
                if (isset($info['path']) && is_file($info['path'])) {
                    @unlink($info['path']);
                }
                unset($_SESSION['cm_preview_files'][$scope][$token]);
            }
        }
    }

    function cm_preview_reset_scope($scope) {
        cm_preview_bootstrap();
        if (!isset($_SESSION['cm_preview_files'][$scope])) {
            return;
        }
        foreach ($_SESSION['cm_preview_files'][$scope] as $info) {
            if (isset($info['path']) && is_file($info['path'])) {
                @unlink($info['path']);
            }
        }
        unset($_SESSION['cm_preview_files'][$scope]);
    }

    function cm_preview_remove_tokens($scope, array $tokens) {
        cm_preview_bootstrap();
        if (!isset($_SESSION['cm_preview_files'][$scope])) {
            return;
        }
        foreach ($tokens as $token) {
            if (isset($_SESSION['cm_preview_files'][$scope][$token])) {
                $info = $_SESSION['cm_preview_files'][$scope][$token];
                if (isset($info['path']) && is_file($info['path'])) {
                    @unlink($info['path']);
                }
                unset($_SESSION['cm_preview_files'][$scope][$token]);
            }
        }
        if (empty($_SESSION['cm_preview_files'][$scope])) {
            unset($_SESSION['cm_preview_files'][$scope]);
        }
    }

    function cm_preview_store_single($fieldName, $scope, $allowedTypes, $maxSizeBytes) {
        cm_preview_bootstrap();
        cm_preview_cleanup($scope);

        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Please choose an image to preview.'];
        }

        $file = $_FILES[$fieldName];
        if ($file['size'] > $maxSizeBytes) {
            return [null, 'File exceeds the allowed size limit.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : $file['type'];
        if ($finfo) { finfo_close($finfo); }
        if (!isset($allowedTypes[$mime])) {
            return [null, 'Unsupported file format.'];
        }

        $token = bin2hex(random_bytes(16));
        $ext = $allowedTypes[$mime];
        $dir = cm_preview_dir($scope);
        $filename = $token . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return [null, 'Unable to generate preview.'];
        }

        $url = '/carriemart/uploads/tmp_previews/' . $scope . '/' . $filename;
        $_SESSION['cm_preview_files'][$scope][$token] = [
            'path' => $dest,
            'url' => $url,
            'ext' => $ext,
            'created' => time()
        ];

        return [$token, null];
    }

    function cm_preview_store_multiple($fieldName, $scope, $allowedTypes, $maxSizeBytes, $maxFiles = 10) {
        cm_preview_bootstrap();
        cm_preview_cleanup($scope);

        if (!isset($_FILES[$fieldName])) {
            return [[], 'Please choose at least one image to preview.'];
        }

        $files = $_FILES[$fieldName];
        $entries = [];

        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                $entries[] = [
                    'name'  => $files['name'][$i],
                    'type'  => $files['type'][$i] ?? '',
                    'tmp'   => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size'  => $files['size'][$i]
                ];
            }
        } else {
            $entries[] = [
                'name'  => $files['name'],
                'type'  => $files['type'] ?? '',
                'tmp'   => $files['tmp_name'],
                'error' => $files['error'],
                'size'  => $files['size']
            ];
        }

        $entries = array_filter($entries, function ($entry) {
            return $entry['error'] !== UPLOAD_ERR_NO_FILE;
        });

        if (empty($entries)) {
            return [[], 'Please choose at least one image to preview.'];
        }

        if ($maxFiles > 0 && count($entries) > $maxFiles) {
            return [[], 'Too many files selected for preview.'];
        }

        $tokens = [];
        $firstError = null;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        foreach ($entries as $entry) {
            if ($entry['error'] !== UPLOAD_ERR_OK) {
                if ($firstError === null) $firstError = 'Unable to process one of the images.';
                continue;
            }
            if ($entry['size'] > $maxSizeBytes) {
                if ($firstError === null) $firstError = 'One of the images exceeds the size limit.';
                continue;
            }
            $mime = $entry['type'];
            if ($finfo && is_file($entry['tmp'])) {
                $detected = finfo_file($finfo, $entry['tmp']);
                if ($detected !== false) {
                    $mime = $detected;
                }
            }
            if (!isset($allowedTypes[$mime])) {
                if ($firstError === null) $firstError = 'Unsupported file format found.';
                continue;
            }

            $token = bin2hex(random_bytes(16));
            $ext = $allowedTypes[$mime];
            $dir = cm_preview_dir($scope);
            $filename = $token . '.' . $ext;
            $dest = $dir . DIRECTORY_SEPARATOR . $filename;

            if (!move_uploaded_file($entry['tmp'], $dest)) {
                if ($firstError === null) $firstError = 'Unable to store one of the preview images.';
                continue;
            }

            $url = '/carriemart/uploads/tmp_previews/' . $scope . '/' . $filename;
            $_SESSION['cm_preview_files'][$scope][$token] = [
                'path' => $dest,
                'url' => $url,
                'ext' => $ext,
                'created' => time()
            ];
            $tokens[] = $token;
        }

        if ($finfo) {
            finfo_close($finfo);
        }

        if (empty($tokens)) {
            return [[], $firstError ?? 'Unable to generate previews.'];
        }

        return [$tokens, $firstError];
    }

    function cm_preview_get_url($scope, $token) {
        cm_preview_bootstrap();
        if (isset($_SESSION['cm_preview_files'][$scope][$token])) {
            return $_SESSION['cm_preview_files'][$scope][$token]['url'] ?? null;
        }
        return null;
    }

    function cm_preview_consume($scope, $token) {
        cm_preview_bootstrap();
        if (!isset($_SESSION['cm_preview_files'][$scope][$token])) {
            return null;
        }
        $info = $_SESSION['cm_preview_files'][$scope][$token];
        unset($_SESSION['cm_preview_files'][$scope][$token]);
        return $info;
    }
}

