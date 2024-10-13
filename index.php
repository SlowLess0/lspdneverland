<?php

session_start();

setcookie('darkMode', '1', time() + (86400 * 30), "/");

if(!isset($_SESSION["username"])){
    header("Location: login.php");
}

require("action/mysql.php");

$users=$bdd->query("SELECT * FROM users ORDER BY id ASC");
$getSaisies=$bdd->query("SELECT * FROM saisies ORDER BY id ASC");
$inService = $bdd->prepare("SELECT * FROM users WHERE status != ? ORDER BY id ASC");
$inService->execute(array(0));

$me=$bdd->prepare("SELECT * FROM users WHERE username = ?");
$me->execute(array($_SESSION["username"]));

$me = $me->fetch();

if(isset($_GET["id"]) and $_GET["id"] != null){
    $getUser=$bdd->prepare("SELECT * FROM users WHERE id = ?");
    $getUser->execute(array(htmlspecialchars($_GET["id"])));

    if($getUser->rowCount() >= 1){
        $selectedUser = $getUser->fetch();
    }else{
        $errorMsg = "L'utilisateur fourni est introuvable !";
    }
}

if(isset($_POST["confirm"])){
    if(isset($_POST["fullname"]) and $_POST["fullname"] != null and isset($_POST["grade"]) and $_POST["grade"] != null and isset($_POST["matricule"]) and $_POST["matricule"] != null and isset($_POST["status"]) and $_POST["status"] != null){
        $fullname = htmlspecialchars($_POST["fullname"]);
        $grade = htmlspecialchars($_POST["grade"]);
        $matricule = htmlspecialchars($_POST["matricule"]);
        $status = htmlspecialchars($_POST["status"]);
        if(isset($_POST["affectation"])){ $affectation = htmlspecialchars($_POST["affectation"]); }else{ $affectation = null; }
        if(isset($_POST["specialite"])){ $specialite = htmlspecialchars($_POST["specialite"]); }else{ $specialite = null; }
        if(isset($_POST["formateur"])){ $formateur = htmlspecialchars($_POST["formateur"]); }else{ $formateur = null; }
        $ppa = 0;
        if(isset($_POST["ppa"])){
            $ppa = 1;
        }
        $conduite = 0;
        if(isset($_POST["conduite"])){
            $conduite = 1;
        }
        if(isset($_POST["notes"])){ $notes = htmlspecialchars($_POST["notes"]); }else{ $notes = null; }

        if(isset($selectedUser)){
            $editUserInfos = $bdd->prepare("UPDATE users SET fullname = ?, grade = ?, matricule = ?, status = ?, affectation = ?, specialites = ?, ppa = ?, conduite = ?, notes = ? WHERE id = ?");
            $editUserInfos->execute(array($fullname, $grade, $matricule, $status, $affectation, $specialite, $ppa, $conduite, $notes, $selectedUser["id"]));

            $successMsg = "L'utilisateur ".$fullname." a bien été modifié.";
            header("Location: index.php");
        }
    }
}

if(isset($_POST["saisiesubmit"])){
    if(isset($_POST["saisiename"]) && $_POST["saisiename"] != null and isset($_POST["saisieamount"]) && $_POST["saisieamount"] != null and isset($_POST["saisiecategory"]) && $_POST["saisiecategory"] != null){
        $name = htmlspecialchars($_POST["saisiename"]);
        $amount = htmlspecialchars($_POST["saisieamount"]);
        $description = null;
        if(isset($_POST["saisiedescription"]) && !empty($_POST["saisiedescription"])){
            $description = htmlspecialchars($_POST["saisiedescription"]);
        }
        $category = htmlspecialchars($_POST["saisiecategory"]);

        $checkIfAlreadyExist=$bdd->prepare("SELECT * FROM saisies WHERE name = ? ORDER BY category ASC");
        $checkIfAlreadyExist->execute(array(strtolower($name)));

        if($checkIfAlreadyExist->rowCount() >= 1){
            $updateSaisie=$bdd->prepare("UPDATE saisies SET amount = ? WHERE name = ?");
            $updateSaisie->execute(array($checkIfAlreadyExist->fetch()["amount"] += $amount, strtolower($name)));
            header("Location: index.php");
        }else{
            $addSaisie=$bdd->prepare("INSERT INTO saisies (name, amount, description, category) VALUES (?,?,?,?)");
            $addSaisie->execute(array($name, $amount, $description, $category));
            header("Location: index.php");
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Los Santos Police Department</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="import/style.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body>
    <form method="POST">
        <div class="modal fade" id="saisiesModal" tabindex="-1" role="dialog" aria-labelledby="saisiesLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saisiesLabel">Ajouter une saisie</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-floating">
                        <input type="text" class="form-control" autocomplete="off" placeholder="Nom" id="formlabel" name="saisiename"/>
                        <label for="floatingTextarea">Nom</label>
                    </div><br>
                    <div class="form-floating">
                        <input type="number" class="form-control" autocomplete="off" placeholder="Montant" id="formlabel" name="saisieamount"/>
                        <label for="floatingTextarea">Montant</label>
                    </div><br>
                    <div class="form-floating">
                        <input type="text" class="form-control" autocomplete="off" placeholder="Nom" id="formlabel" name="saisiedescription"/>
                        <label for="floatingTextarea">Description</label>
                    </div><br>
                    <div class="form-floating">
                        <select class="form-select" id="floatingSelect" name="saisiecategory" aria-label="Sélectionnez la catégorie de votre saisie">
                            <option value="Drogue" selected>Drogue</option>
                            <option value="Arme">Arme</option>
                            <option value="Autre">Autre</option>
                        </select>
                        <label for="floatingSelect">Catégorie</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" name="saisiesubmit" class="btn btn-primary">Ajouter la saisie</button>
                </div>
                </div>
            </div>
        </div>
    </form>
    <?php
    if(isset($selectedUser)){
        ?>
        <form method="POST">
        <div class="editusermodal"><div class="modal-backdrop show"></div>
        <div class="modal modalusers" id="editPlayer" style="display: block !important;">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPlayerLabel">Modifier <?= $selectedUser["fullname"] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer" onclick="closeUserModal()"></button>
                </div>
                <div class="modal-body">
                <form method="POST">
                    <div class="form-floating">
                        <input type="text" class="form-control" autocomplete="off" placeholder="Nom" id="formlabel" name="fullname" value="<?= $selectedUser["fullname"] ?>"/>
                        <label for="floatingTextarea">Nom</label>
                    </div><br>
                    <div class="form-floating">
                        <input type="tetx" class="form-control" autocomplete="off" placeholder="Grade" id="formlabel" name="grade" value="<?= $selectedUser["grade"] ?>"/>
                        <label for="floatingTextarea">Grade</label>
                    </div><br>
                    <div class="form-floating">
                        <input type="tetx" class="form-control" autocomplete="off" placeholder="Matricule" id="formlabel" name="matricule" value="<?= $selectedUser["matricule"] ?>"/>
                        <label for="floatingTextarea">Matricule</label>
                    </div><br>
                    <div class="form-floating">
                        <select class="form-select" id="floatingSelect" name="status" aria-label="Sélectionnez le status de l'officier">
                            <option value="0" <?php if($selectedUser["status"] == 0){ echo("selected"); } ?>>Hors service</option>
                            <option value="1"  <?php if($selectedUser["status"] == 1){ echo("selected"); } ?>>En patrouille</option>
                            <option value="2"  <?php if($selectedUser["status"] == 2){ echo("selected"); } ?>>En route</option>
                            <option value="3"  <?php if($selectedUser["status"] == 3){ echo("selected"); } ?>>Sur place</option>
                            <option value="4"  <?php if($selectedUser["status"] == 4){ echo("selected"); } ?>>Indisponible</option>
                        </select>
                        <label for="floatingSelect">Status de l'officier</label>
                    </div><br>
                    <div class="form-floating">
                        <select class="form-select" id="floatingSelect" name="affectation" aria-label="Sélectionnez l'affectation de l'officier'">
                            <option value="" <?php if($selectedUser["affectation"] == null || $selectedUser["affectation"] == ""){ echo("selected"); } ?>></option>
                            <option value="Lincoln-01" <?php if($selectedUser["affectation"] == "Lincoln-01"){ echo("selected"); } ?>>Lincoln-01</option>
                            <option value="Lincoln-02" <?php if($selectedUser["affectation"] == "Lincoln-02"){ echo("selected"); } ?>>Lincoln-02</option>
                            <option value="Adam-01" <?php if($selectedUser["affectation"] == "Adam-01"){ echo("selected"); } ?>>Adam-01</option>
                            <option value="Adam-02" <?php if($selectedUser["affectation"] == "Adam-02"){ echo("selected"); } ?>>Adam-02</option>
                            <option value="Adam-03" <?php if($selectedUser["affectation"] == "Adam-03"){ echo("selected"); } ?>>Adam-03</option>
                            <option value="Adam-04" <?php if($selectedUser["affectation"] == "Adam-04"){ echo("selected"); } ?>>Adam-04</option>
                            <option value="Tango-01" <?php if($selectedUser["affectation"] == "Tango-01"){ echo("selected"); } ?>>Tango-01</option>
                            <option value="Tango-02" <?php if($selectedUser["affectation"] == "Tango-02"){ echo("selected"); } ?>>Tango-02</option>
                            <option value="Mary-01" <?php if($selectedUser["affectation"] == "Mary-01"){ echo("selected"); } ?>>Mary-01</option>
                            <option value="Mary-02" <?php if($selectedUser["affectation"] == "Mary-02"){ echo("selected"); } ?>>Mary-02</option>
                            <option value="Hector-01" <?php if($selectedUser["affectation"] == "Hector-01"){ echo("selected"); } ?>>Hector-01</option>
                            <option value="Predator-01" <?php if($selectedUser["affectation"] == "Predator-01"){ echo("selected"); } ?>>Predator-01</option>
                            <option value="David-01" <?php if($selectedUser["affectation"] == "David-01"){ echo("selected"); } ?>>David-01</option>
                            <option value="David-02" <?php if($selectedUser["affectation"] == "David-02"){ echo("selected"); } ?>>David-02</option>
                        </select>
                        <label for="floatingSelect">Affectation de l'officier</label>
                    </div><br>
                    <div class="form-floating">
                        <input type="tetx" class="form-control" autocomplete="off" placeholder="Spécialité" id="formlabel" name="specialite" value="<?= $selectedUser["specialites"] ?>"/>
                        <label for="floatingTextarea">Spécialité</label>
                    </div><br>
                    <div class="form-floating">
                        <input type="tetx" class="form-control" autocomplete="off" placeholder="Formateur" id="formlabel" name="formateur" value="<?= $selectedUser["formateur"] ?>"/>
                        <label for="floatingTextarea">Formateur</label>
                    </div><br>
                    <?php
                    if($selectedUser["ppa"] == 1){
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="ppa" id="ppa" checked>
                            <label class="form-check-label" for="ppa">
                                Permis de Port d'Armes
                            </label>
                        </div>
                        <?php
                    }else{
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="ppa" id="ppa">
                            <label class="form-check-label" for="ppa">
                                Permis de Port d'Armes
                            </label>
                        </div>
                        <?php
                    }

                    if($selectedUser["conduite"] == 1){
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="conduite" id="conduite" checked>
                            <label class="form-check-label" for="conduite">
                                Conduite
                            </label>
                        </div><br>
                        <?php
                    }else{
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="conduite" id="conduite">
                            <label class="form-check-label" for="conduite">
                                Conduite
                            </label>
                        </div><br>
                        <?php
                    }
                    ?>
                    <div class="form-floating">
                        <input type="tetx" class="form-control" autocomplete="off" placeholder="Notes" id="formlabel" name="notes" value="<?= $selectedUser["notes"] ?>"/>
                        <label for="floatingTextarea">Notes</label>
                    </div>
                </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeUserModal()">Fermer</button>
                    <button type="submit" class="btn btn-primary" name="confirm" onclick="closeUserModal()">Enregister</button>
                </div>
                </div>
            </div>
        </div></div></form>
        <?php
    }
    ?>



    <nav class="sidebar close" style="opacity: 1 !important;">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="img/logo.png" alt="jsp" onclick="window.location.href='index.php'">
                </span>
                <div class="text logo-text">
                    <span class="name"><?= $_SESSION["fullname"] ?></span>
                    <span class="profession"><?= $_SESSION["grade"] ?></span>
                </div>
            </div>
            <i class='bx bx-chevron-right toggle'></i>
        </header>
        <div class="menu-bar">
            <div class="menu">
                <li class="search-box">
                    <i class='bx bx-search icon'></i>
                    <input type="text" placeholder="Rechercher...">
                </li>
                <li class="nav-link">
                    <a href="#" onclick="openSection('dashboard')" class="active" id="dashboard">
                        <i class='bx bx-home-alt icon' ></i>
                        <span class="text nav-text">Tableau de bord</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="#" onclick="openSection('central')" id="central">
                        <i class='bx bx-table icon'></i>
                        <span class="text nav-text">Central</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="#" onclick="openSection('saisies')" id="saisies">
                        <i class='bx bx-label icon'></i>
                        <span class="text nav-text">Saisies</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="#" onclick="openSection('weapons')" id="weapons">
                      <i class='bx bx-water icon'></i>
                        <span class="text nav-text">Armes enregistrés</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="#" onclick="openSection('dashboard')">
                        <i class='bx bx-heart icon' ></i>
                        <span class="text nav-text">Likes</span>
                    </a>
                </li>
                <?php
                if($_SESSION["admin"] == true){
                    ?>
                    <li class="nav-link">
                        <a href="admin.php">
                            <i class='bx bx-cog icon'></i>
                            <span class="text nav-text">Panel admin</span>
                        </a>
                    </li>
                    <?php
                }
                ?>
            </div>
            <div class="bottom-content">
                <li class="">
                    <a href="logout.php">
                        <i class='bx bx-log-out icon' ></i>
                        <span class="text nav-text">Se déconnecter</span>
                    </a>
                </li>
                <li class="mode">
                    <div class="sun-moon">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Dark mode</span>
                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>
                
            </div>
        </div>
    </nav>
    <section class="home" id="dashboardSection">
        <div class="text">Tableau de bord</div>

        <h1>Mon profile:</h1>
        <div class="d-flex justify-content-between">
            <p>Nom: <strong><?= $me["fullname"] ?></strong></p>
            <p>Grade: <strong><?= $me["grade"] ?></strong></p>
            <p>Affectation: <strong><?php if(empty($me["affectation"])){ echo("///"); }else{ echo $me["affectation"]; } ?></strong></p>
            <p>Status: <?php
                if($me["status"] == 0){
                    ?>
                    <span class="badge text-bg-danger rounded-pill d-inline">Hors service</span>
                <?php
                }elseif ($me["status"] == 1) {
                    ?>
                    <span class="badge text-bg-success rounded-pill d-inline">En patrouille</span>
                    <?php
                }elseif ($me["status"] == 2) {
                    ?>
                    <span class="badge text-bg-primary rounded-pill d-inline">En route</span>
                    <?php
                }elseif ($me["status"] == 3) {
                    ?>
                    <span class="badge text-bg-warning rounded-pill d-inline">Sur place</span>
                    <?php
                }elseif ($me["status"] == 4) {
                    ?>
                    <span class="badge text-bg-secondary rounded-pill d-inline">Indisponible</span>
                    <?php
                }
            ?></p>
            
        </div>

        <h1>Officiers en service:</h1>
        <table class="table align-middle mb-0 table-hover userstable2">
            <thead class="bg-light">
                <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Grade</th>
                <th>Matricule</th>
                <th>Status</th>
                <th>Affectation</th>
                <th>Spécialités</th>
                <th>Formé par</th>
                <th>PPA</th>
                <th>Conduite</th>
                <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($inService->rowCount() == 0){
                    ?>
                    <tr>
                        <td>Aucun officier n'est actuellement en service</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php
                }else{
                    while($user = $inService->fetch()){
                        $link = "?id=".$user['id'];
                        ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='<?=$link;?>'">
                            <td><?=$user['id']?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                <div class="ms-3">
                                    <p class="fw-bold mb-1"><?=$user['fullname']?></p>
                                </div>
                                </div>
                            </td>
                            <td><?=$user['grade']?></td>
                            <td>
                                <p class="fw-normal mb-1"><?=$user['matricule']?></p>
                            </td>
                            <td>
                                <?php
                                if($user["status"] == 0){
                                    ?>
                                    <span class="badge text-bg-danger rounded-pill d-inline">Hors service</span>
                                <?php
                                }elseif ($user["status"] == 1) {
                                    ?>
                                    <span class="badge text-bg-success rounded-pill d-inline">En patrouille</span>
                                    <?php
                                }elseif ($user["status"] == 2) {
                                    ?>
                                    <span class="badge text-bg-primary rounded-pill d-inline">En route</span>
                                    <?php
                                }elseif ($user["status"] == 3) {
                                    ?>
                                    <span class="badge text-bg-warning rounded-pill d-inline">Sur place</span>
                                    <?php
                                }elseif ($user["status"] == 4) {
                                    ?>
                                    <span class="badge text-bg-secondary rounded-pill d-inline">Indisponible</span>
                                    <?php
                                }
                                ?>
                                
                            </td>
                            <td><?php if(isset($user['affectation']) && $user["affectation"] != ""){echo $user["affectation"];}else{echo "///";}?></td>
                            <td><?php if(isset($user['specialites']) && $user["specialites"] != ""){echo $user["specialites"];}else{echo "///";}?></td>
                            <td><?php if(isset($user['formateur']) && $user["formateur"] != ""){echo $user["formateur"];}else{echo "///";}?></td>
                            <td>
                                <?php
                                if($user["ppa"] == 0){
                                    ?>
                                    <input type="checkbox" disabled>
                                    <?php
                                }else{
                                    ?>
                                    <input type="checkbox" disabled checked>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                            <?php
                                if($user["conduite"] == 0){
                                    ?>
                                    <input type="checkbox" disabled>
                                    <?php
                                }else{
                                    ?>
                                    <input type="checkbox" disabled checked>
                                    <?php
                                }
                                ?>
                            </td>
                            <td><?php if(isset($user['notes']) && $user["notes"] != ""){echo $user["notes"];}else{echo "///";}?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                
            </tbody>
            </table>
    </section>
    
    <section class="home" id="centralSection" style="display: none;">
        <div class="text">Central</div>

        <table class="table align-middle mb-0 table-hover userstable">
            <thead class="bg-light">
                <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Grade</th>
                <th>Matricule</th>
                <th>Status</th>
                <th>Affectation</th>
                <th>Spécialités</th>
                <th>Formé par</th>
                <th>PPA</th>
                <th>Conduite</th>
                <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while($user = $users->fetch()){
                    $link = "?id=".$user['id'];
                    ?>
                    <tr style="cursor: pointer;" onclick="window.location.href='<?=$link;?>'">
                        <td><?=$user['id']?></td>
                        <td>
                            <div class="d-flex align-items-center">
                            <div class="ms-3">
                                <p class="fw-bold mb-1"><?=$user['fullname']?></p>
                            </div>
                            </div>
                        </td>
                        <td><?=$user['grade']?></td>
                        <td>
                            <p class="fw-normal mb-1"><?=$user['matricule']?></p>
                        </td>
                        <td>
                            <?php
                            if($user["status"] == 0){
                                ?>
                                <span class="badge text-bg-danger rounded-pill d-inline">Hors service</span>
                            <?php
                            }elseif ($user["status"] == 1) {
                                ?>
                                <span class="badge text-bg-success rounded-pill d-inline">En patrouille</span>
                                <?php
                            }elseif ($user["status"] == 2) {
                                ?>
                                <span class="badge text-bg-primary rounded-pill d-inline">En route</span>
                                <?php
                            }elseif ($user["status"] == 3) {
                                ?>
                                <span class="badge text-bg-warning rounded-pill d-inline">Sur place</span>
                                <?php
                            }elseif ($user["status"] == 4) {
                                ?>
                                <span class="badge text-bg-secondary rounded-pill d-inline">Indisponible</span>
                                <?php
                            }
                            ?>
                            
                        </td>
                        <td><?php if(isset($user['affectation']) && $user["affectation"] != ""){echo $user["affectation"];}else{echo "///";}?></td>
                        <td><?php if(isset($user['specialites']) && $user["specialites"] != ""){echo $user["specialites"];}else{echo "///";}?></td>
                        <td><?php if(isset($user['formateur']) && $user["formateur"] != ""){echo $user["formateur"];}else{echo "///";}?></td>
                        <td>
                            <?php
                            if($user["ppa"] == 0){
                                ?>
                                <input type="checkbox" disabled>
                                <?php
                            }else{
                                ?>
                                <input type="checkbox" disabled checked>
                                <?php
                            }
                            ?>
                        </td>
                        <td>
                        <?php
                            if($user["conduite"] == 0){
                                ?>
                                <input type="checkbox" disabled>
                                <?php
                            }else{
                                ?>
                                <input type="checkbox" disabled checked>
                                <?php
                            }
                            ?>
                        </td>
                        <td><?php if(isset($user['notes']) && $user["notes"] != ""){echo $user["notes"];}else{echo "///";}?></td>
                    </tr>
                    <?php
                }
                ?>
                
            </tbody>
            </table>
    </section>

    <section class="home" id="saisiesSection" style="display: none;">
        <div class="d-flex flex-row-reverse">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#saisiesModal">Ajouter une saisie</button>
        </div><br>
        <table class="table align-middle mb-0 table-hover saisiestable">
            <thead class="bg-light">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Montant</th>
                    <th>Catégorie</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($getSaisies->rowCount() == 0){
                    ?>
                    <tr>
                        <td>Aucune donnée dans les saisies...</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php
                }else{
                    while($saisies = $getSaisies->fetch()){
                        ?>
                        <tr style="cursor: pointer;">
                            <td><?=$saisies['id'];?></td>
                            <td><?=$saisies['name'];?></td>
                            <td><?=$saisies['amount'];?></td>
                            <td><?=$saisies['category'];?></td>
                            <td><?php if(isset($saisies['description']) && $saisies['description'] != ""){ echo $saisies['description']; }else{ echo "///"; } ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                
            </tbody>
        </table>
    </section>

    <section class="home" id="weaponsSection" style="display: none;">
        <div class="d-flex flex-row-reverse">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#weaponsModal">Ajouter une saisie</button>
        </div><br>
        <table class="table align-middle mb-0 table-hover weaponstable">
            <thead class="bg-light">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Montant</th>
                    <th>Catégorie</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($getSaisies->rowCount() == 0){
                    ?>
                    <tr>
                        <td>Aucune donnée dans les saisies...</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php
                }else{
                    while($saisies = $getSaisies->fetch()){
                        ?>
                        <tr style="cursor: pointer;">
                            <td><?=$saisies['id'];?></td>
                            <td><?=$saisies['name'];?></td>
                            <td><?=$saisies['amount'];?></td>
                            <td><?=$saisies['category'];?></td>
                            <td><?php if(isset($saisies['description']) && $saisies['description'] != ""){ echo $saisies['description']; }else{ echo "///"; } ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                
            </tbody>
        </table>
    </section>


    <script>
        const body = document.querySelector('body'),
        sidebar = body.querySelector('nav'),
        toggle = body.querySelector(".toggle"),
        searchBtn = body.querySelector(".search-box"),
        modeSwitch = body.querySelector(".toggle-switch"),
        modeText = body.querySelector(".mode-text");
        toggle.addEventListener("click" , () =>{
            sidebar.classList.toggle("close");
        })
        searchBtn.addEventListener("click" , () =>{
            sidebar.classList.remove("close");
        })
        modeSwitch.addEventListener("click" , () =>{
            body.classList.toggle("dark");
            document.querySelector(".saisiestable").classList.toggle("table-dark");
            document.querySelector(".weaponstable").classList.toggle("bg-dark");
            document.querySelector(".userstable").classList.toggle("table-dark");

            if(body.classList.contains("dark")){
                modeText.innerText = "Light mode";
                fetch('./action/update_cookie.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'darkMode=1'
                })
                .then(response => {
                })
                .catch(error => {
                    console.error('Erreur lors de la mise à jour du cookie :', error);
                });
            } else {
                modeText.innerText = "Dark mode";
                fetch('./action/update_cookie.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'darkMode=0'
                })
                .then(response => {
                    // Gérer la réponse du serveur (si nécessaire)
                })
                .catch(error => {
                    console.error('Erreur lors de la mise à jour du cookie :', error);
                });
            }
        });


        <?php
            if (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] == '1') {
                ?>
                body.classList.toggle("dark");
                document.querySelector(".saisiestable").classList.toggle("table-dark");
                document.querySelector(".userstable").classList.toggle("table-dark");
                document.querySelector(".userstable2").classList.toggle("table-dark");
                document.querySelector(".modalsaisies").classList.toggle("bg-dark");
                document.querySelector(".weaponstable").classList.toggle("bg-dark");

                if(body.classList.contains("dark")){
                    modeText.innerText = "Light mode";
                }else{
                    modeText.innerText = "Dark mode";
                    
                }
                <?php
            }
        ?>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="import/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>