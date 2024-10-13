<?php
session_start();

if(isset($_SESSION["username"])){
    header("Location: index.php");
}

if(isset($_POST["submit"])){
    if(isset($_POST["username"]) and $_POST['username'] != null and isset($_POST["password"]) and $_POST["password"] != null){
        require("action/mysql.php");

        $checkUser=$bdd->prepare("SELECT * FROM users WHERE username = ?");
        $checkUser->execute(array(htmlspecialchars($_POST["username"])));

        if($checkUser->rowCount() >= 1){
            $getInfos=$checkUser->fetch();

            if($getInfos["password"] == htmlspecialchars($_POST["password"])){
                $_SESSION["username"] = $getInfos["username"];
                $_SESSION["matricule"] = $getInfos["matricule"];
                $_SESSION["fullname"] = $getInfos["fullname"];
                $_SESSION["grade"] = $getInfos["grade"];
                $_SESSION["password"] = $getInfos["password"];
                $_SESSION["admin"] = $getInfos["admin"];

                header("Location: index.php");
            }else{
                $errorMsg = "Mot de passe incorrecte !";
            }
        }else{
            $errorMsg = "Nom d'utilisateur incorrecte !";
        }
    }else{
        $errorMsg = "Veuillez renseigner tous les champs !";
    }
}

?> 

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Los Santos Police Department - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="import/login.css">
</head>
<body>
    <section class="container bg-white rounded-4 align-middle p-4 w-50">
        <form method="POST">
            <img src="img/logo.png" alt="LSPD.png" class="logo mx-auto d-block"><br>
            <?php
            if(isset($errorMsg)){
                ?>
                <div class="alert alert-danger" role="alert">
                    <?= $errorMsg ?>
                </div>
                <?php
            }else{
                ?><br><br><?php
            }
            ?>
            <div class="form-floating">
                <input type="text" class="form-control" autocomplete="off" placeholder="Nom d'utilisateur" id="username" name="username"/>
                <label for="floatingTextarea">Nom d'utilisateur</label>
            </div><br>
            <div class="form-floating">
                <input type="password" class="form-control" placeholder="Mot de passe" id="username" name="password"/>
                <label for="floatingTextarea">Mot de passe</label>
            </div><br>
            <div class="d-grid gap-2">
                <button class="btn btn-primary" type="submit" name="submit">Se connecter</button>
            </div>
        </form>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
</body>
</html>