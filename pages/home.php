<?php
session_start();

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    header("Location: ../index.php");
    exit;
}
include('../lay/menu.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Status</title>
</head>

<body class="bg2 min-vh-100 d-flex flex-column justify-content-center">
    <div class="container bgcont rounded d-flex flex-column gap-4">
        <div class="w-100 h-50 d-flex align-items-bottom justify-content-evenly p-2">

            <div class="bg1 w-25 rounded medout"></div>

            <div class="w-50 h-100 d-flex justify-content-evenly">
                <div class="w-100 h-100 d-flex flex-column">
                    <div class="w-100 d-flex align-items-center p-0 ps-2 pe-2 justify-content-between">
                        <button class="btn p-0" data-bs-toggle="modal" data-bs-target="#criarChamadoModal">
                            <i class="bi bi-plus-circle icon_plus"></i>
                        </button>
                        <h3 class="w-100 m-0 text-end">Próximos Medicamentos</h3>
                    </div>

                    <div class="modal fade" id="criarChamadoModal" tabindex="-1"
                        aria-labelledby="criarChamadoModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="criarChamadoModalLabel">Adicionar Medicamento</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="../connections/create_med.php" method="POST">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="med_nome" class="form-label">Nome</label>
                                            <input type="text" class="form-control" id="med_nome" name="med_nome"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="med_brand" class="form-label">Marca</label>
                                            <input class="form-control" id="med_brand" name="med_brand" rows="3"
                                                required></input>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Dosagem</label>
                                            <div class="mb-1 d-flex gap-2">
                                                <input type="number" class="form-control" id="med_dosage"
                                                    name="med_dosage" required>
                                                <select class="form-select" id="med_type" name="med_type" required>
                                                    <option value="capsulas">Cápsulas</option>
                                                    <option value="comprimidos">Comprimidos</option>
                                                    <option value="gotas">Gotas</option>
                                                </select>
                                            </div>
                                            <div class="mb-1 d-flex gap-2">
                                                <input type="number" class="form-control" id="med_milligram"
                                                    name="med_milligram" required>
                                                <select class="form-select" id="med_milligram_unit" name="med_milligram_unit" required>
                                                    <option value="mg">mg</option>
                                                    <option value="g">g</option>
                                                    <option value="ml">ml</option>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Dias e Horários</label>
                                            <div class="mb-1 d-flex gap-2">
                                                <input type="number" class="form-control" id="med_dosage"
                                                    name="med_dosage" required>
                                                <select class="form-select" id="med_type" name="med_type" required>
                                                    <option value="capsulas">Cápsulas</option>
                                                    <option value="comprimidos">Comprimidos</option>
                                                    <option value="gotas">Gotas</option>
                                                </select>
                                            </div>
                                            <div class="mb-1 d-flex gap-2">
                                                <input type="number" class="form-control" id="med_milligram"
                                                    name="med_milligram" required>
                                                <select class="form-select" id="med_milligram_unit" name="med_milligram_unit" required>
                                                    <option value="mg">mg</option>
                                                    <option value="g">g</option>
                                                    <option value="ml">ml</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Criar Chamado</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>



                    <div class="bg3 w-100 h-100 rounded">
                        <div class="table-container p-3">
                            <table class="table table-hover">
                                <thead>
                                    <tr class="">
                                        <th scope="col" class="table_med">Medicamento</th>
                                        <th scope="col" class="table_med">Dosagem</th>
                                        <th scope="col" class="table_med text-center">Horário</th>
                                        <th scope="col" class="table_med text-end">Confirmar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    include('../connections/get_med_home.php');
                                    if (!empty($medicamento)) {
                                        foreach ($medicamento as $linha) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($linha['med_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($linha['med_dosage']) . "</td>";
                                            echo "<td>" . htmlspecialchars($linha['med_time']) . "</td>";
                                            echo "<td><button class='btn btn-success btn-sm'>Confirmar</button></td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="w-100 h-50 d-flex align-items-end justify-content-evenly p-2">

            <div class="w-50 h-100 d-flex justify-content-evenly align-items-center">
                <div class="w-100 h-100 d-flex flex-column">
                    <div class="w-100 d-flex align-items-center">
                        <h3 class="">Próximas Consultas</h3>
                    </div>
                    <div class="bg4 w-100 h-100 rounded scrolly">
                    </div>
                </div>
            </div>

            <div class="bg1 w-25 h-100 rounded"></div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>

</html>