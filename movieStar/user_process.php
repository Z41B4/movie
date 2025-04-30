<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);

require_once("globals.php");
require_once("db.php");
require_once("models/User.php");
require_once("models/Message.php");
require_once("dao/UserDAO.php");

$message = new Message($BASE_URL);

$userDao = new UserDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

// Atualizar usuário
if ($type === "update") {

    // Resgata dados do usuário
    $userData = $userDao->verifyToken();

    // Receber dados do post
    $name = filter_input(INPUT_POST, "name");
    $lastname = filter_input(INPUT_POST, "lastname");
    $email = filter_input(INPUT_POST, "email");
    $bio = filter_input(INPUT_POST, "bio");

    // Criar um novo objeto de usuário
    $user = new User();

    // Preencher os dados do usuário
    $userData->name = $name;
    $userData->lastname = $lastname;
    $userData->email = $email;
    $userData->bio = $bio;

    // Upload da imagem
    if (isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])) {

        $image = $_FILES["image"];
        $imageTypes = ["image/jpeg", "image/jpg", "image/png"];
        $jpgArray = ["image/jpeg", "image/jpg"];

        // Checagem de tipo de imagem
        if (in_array($image["type"], $imageTypes)) {

            // Checar se é JPG
            if (in_array($image["type"], $jpgArray)) {

                $imageFile = @imagecreatefromjpeg($image["tmp_name"]);
                if ($imageFile === false) {
                    error_log("Erro ao processar a imagem JPG: " . $image["tmp_name"]);
                    $message->setMessage("Erro ao processar a imagem JPG!", "error", "back");
                    exit;
                }

                // Gerar o nome da imagem
                $imageName = $user->imageGenerateName(".jpg");

                // Definir o caminho absoluto para salvar a imagem
                $imagePath = "/var/www/movie/movieStar/img/users/" . $imageName;

                // Tentar salvar a imagem JPG no diretório
                $saved = imagejpeg($imageFile, $imagePath, 100);

                // Verificar se a imagem foi salva com sucesso
                if (!$saved) {
                    error_log("Erro ao salvar a imagem JPG. Caminho: " . $imagePath);
                    $message->setMessage("Erro ao salvar a imagem JPG no servidor!", "error", "back");
                    exit;
                }

            // Caso a imagem seja PNG
            } else {

                $imageFile = @imagecreatefrompng($image["tmp_name"]);
                if ($imageFile === false) {
                    error_log("Erro ao processar a imagem PNG: " . $image["tmp_name"]);
                    $message->setMessage("Erro ao processar a imagem PNG!", "error", "back");
                    exit;
                }

                // Gerar o nome da imagem
                $imageName = $user->imageGenerateName(".png");

                // Definir o caminho absoluto para salvar a imagem
                $imagePath = "/var/www/movie/movieStar/img/users/" . $imageName;

                // Tentar salvar a imagem PNG no diretório
                $saved = imagepng($imageFile, $imagePath);

                // Verificar se a imagem foi salva com sucesso
                if (!$saved) {
                    error_log("Erro ao salvar a imagem PNG. Caminho: " . $imagePath);
                    $message->setMessage("Erro ao salvar a imagem PNG no servidor!", "error", "back");
                    exit;
                }
            }

            // Salvar o nome da imagem no banco de dados
            $userData->image = $imageName;

        } else {
            $message->setMessage("Tipo inválido de imagem, insira PNG ou JPG!", "error", "back");
        }
    }

    // Atualiza os dados do usuário no banco
    $userDao->update($userData);

// Atualizar senha do usuário
} else if ($type === "changepassword") {

    // Receber dados do post
    $password = filter_input(INPUT_POST, "password");
    $confirmpassword = filter_input(INPUT_POST, "confirmpassword");

    // Resgata dados do usuário
    $userData = $userDao->verifyToken();

    $id = $userData->id;

    if ($password == $confirmpassword) {

        // Criar um novo objeto de usuário
        $user = new User();

        $finalPassword = $user->generatePassword($password);

        $user->password = $finalPassword;
        $user->id = $id;

        $userDao->changePassword($user);

    } else {
        $message->setMessage("As senhas não são iguais!", "error", "back");
    }

} else {
    $message->setMessage("Informações inválidas!", "error", "index.php");
}
