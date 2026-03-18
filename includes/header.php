<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baustellenverwaltung</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            width: 240px;
            flex-shrink: 0;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1.25rem;
            border-radius: 0.375rem;
            margin: 2px 8px;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.15);
        }
        .sidebar .nav-link i { margin-right: 8px; font-size: 1.1rem; }
        .sidebar-brand {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
            padding: 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .main-content { flex: 1; overflow-x: auto; }
        .top-navbar {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1.5rem;
        }
        .flash-message { margin: 1rem 1.5rem 0; }
    </style>
</head>
<body>
<div class="d-flex">
