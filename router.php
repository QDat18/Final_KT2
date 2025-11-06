<?php
// File: router.php - URL Router for clean URLs

function parseURL() {
    $uri = $_SERVER['REQUEST_URI'];
    $uri = parse_url($uri, PHP_URL_PATH);
    $uri = trim($uri, '/');
    
    // Remove query string
    if (($pos = strpos($uri, '?')) !== false) {
        $uri = substr($uri, 0, $pos);
    }
    
    $parts = explode('/', $uri);
    
    // Default
    $controller = 'product';
    $action = 'index';
    $id = null;
    
    // Parse URL patterns
    if (empty($parts[0])) {
        // Root: / -> products/index
        $controller = 'product';
        $action = 'index';
    }
    elseif ($parts[0] === 'products') {
        $controller = 'product';
        
        if (!isset($parts[1])) {
            // /products -> index
            $action = 'index';
        }
        elseif ($parts[1] === 'create') {
            // /products/create -> create
            $action = 'create';
        }
        elseif ($parts[1] === 'import') {
            // /products/import -> import
            $action = 'import';
        }
        elseif ($parts[1] === 'export-template') {
            // /products/export-template -> export_template
            $action = 'export_template';
        }
        elseif (is_numeric($parts[1])) {
            // /products/{id} -> edit
            $action = 'edit';
            $id = $parts[1];
            
            if (isset($parts[2]) && $parts[2] === 'delete') {
                // /products/{id}/delete -> delete
                $action = 'delete';
            }
        }
    }
    elseif ($parts[0] === 'variants') {
        $controller = 'variant';
        
        if (!isset($parts[1]) || $parts[1] === 'create') {
            // /variants/create -> ajax_store
            $action = 'ajax_store';
        }
        elseif (is_numeric($parts[1])) {
            $id = $parts[1];
            
            if (isset($parts[2]) && $parts[2] === 'delete') {
                // /variants/{id}/delete -> ajax_delete
                $action = 'ajax_delete';
            } else {
                // /variants/{id} -> ajax_update
                $action = 'ajax_update';
            }
        }
    }
    elseif ($parts[0] === 'images') {
        $controller = 'image';
        
        if (!isset($parts[1]) || $parts[1] === 'create') {
            // /images/create -> ajax_store
            $action = 'ajax_store';
        }
        elseif (is_numeric($parts[1])) {
            $id = $parts[1];
            
            if (isset($parts[2]) && $parts[2] === 'delete') {
                // /images/{id}/delete -> ajax_delete
                $action = 'ajax_delete';
            }
        }
    }
    
    return [
        'controller' => $controller,
        'action' => $action,
        'id' => $id
    ];
}

/**
 * Get controller and action from URL or $_GET
 */
function getRoute() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['controller'])) {
        return [
            'controller' => $_POST['controller'],
            'action' => $_POST['action'] ?? 'index'
        ];
    }
    if (!isset($_GET['controller'])) {
        $route = parseURL();
        $_GET['controller'] = $route['controller'];
        $_GET['action'] = $route['action'];
        if ($route['id']) {
            $_GET['id'] = $route['id'];
        }
    }

    $controller = $_GET['controller'] ?? 'product';
    $action = $_GET['action'] ?? 'index';

    return [
        'controller' => $controller,
        'action' => $action
    ];
}