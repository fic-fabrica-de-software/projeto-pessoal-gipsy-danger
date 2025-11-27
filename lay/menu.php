<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>MedSet</title>
</head>

<body>
    <nav class="navbar navbar-dark fixed-top m-4">
        <div class="container-fluid">

            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-start p-2 bg1" tabindex="-1" id="offcanvasDarkNavbar"
                aria-labelledby="offcanvasDarkNavbarLabel">
                <div class="offcanvas-header">
                    <h2 class="offcanvas-title t1" id="offcanvasDarkNavbarLabel">Menu</h2>
                    <button type="button" class="btn-close btn-close-white me-2" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>
                <div class="offcanvas-body pt-4 d-flex flex-column justify-content-between h-100">
                    <ul class="navbar-nav flex-grow-1 pe-3">
                        <li class="nav-item w-75">
                            <a class="nav-link fs-5 t1" href="../pages/home.php">Dashboard</a>
                        </li>
                        <li class="nav-item w-75">
                            <a class="nav-link fs-5 t1 pt-5" href="../pages/med.php">Medicamentos</a>
                        </li>
                        <li class="nav-item w-75">
                            <a class="nav-link fs-5 t1 pt-5" href="../pages/appointments.php">Consultas</a>
                        </li>
                        <li class="nav-item w-75">
                            <a class="nav-link fs-5 t1 pt-5" href="../pages/doctors.php">Médicos</a>
                        </li>
                    </ul>
                    <div class="">
                        <div class="bg3 p-3 rounded mb-3 perfil">
                            <a href="" class="fs-5 text-decoration-none d-flex align-items-center">
                                <i class="bi bi-person-circle iconperfil"></i>
                                <?php echo '<div class="d-flex flex-column justify-content-center ps-3"><h5 class="p-0 m-0">' . $_SESSION['user_name'] . '</h5>
                                <h6 class="p-0 m-0">' . $_SESSION['user_email'] . '</h6></div>'; ?>
                            </a>
                        </div>
                        <a href="../connections/exit.php" class="btn-danger btn w-100">Sair</a>
                    </div>

                </div>
            </div>
        </div>
    </nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>

</html>