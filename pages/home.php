<?php
session_start();
include("../lay/menu.php");

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/status.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Sans:wght@200..800&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Status</title>
</head>

<body class="backgroundf min-vh-100 d-flex flex-column justify-content-center">
    <div class="container bgcont rounded p-4 d-flex flex-column gap-4">
        <div class="w-100 h-100 d-flex align-items-center gap-4">
            <div class="bg-danger w-100 h-100 rounded medout"></div>
            <div class="bg-danger w-100 h-100 rounded "></div>
            <div class="bg-danger w-100 h-100 rounded"></div>
        </div>
        <div class="w-100 h-100 d-flex align-items-center gap-4">
            <div class="w-100 h-100 d-flex justify-content-evenly align-items-center">
                <div class="w-100 h-100 d-flex flex-column">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <h3 class="montserrat">Próximos Medicamentos</h3>
                    </div>
                    <div class="bg-danger w-100 h-100 rounded">
                    </div>
                </div>
            </div>
            <div class="w-100 h-100 d-flex justify-content-evenly align-items-center">
                <div class="w-100 h-100 d-flex flex-column">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <h3 class="text">Próximos Consultas</h3>
                    </div>
                    <div class="bg-danger w-100 h-100 rounded">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>