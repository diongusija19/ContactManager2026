<?php
    session_start();

    $first_name = filter_input(INPUT_POST, 'first_name');
    $last_name = filter_input(INPUT_POST, 'last_name');
    $email_address = filter_input(INPUT_POST, 'email_address');
    $phone_number = filter_input(INPUT_POST, 'phone_number');
    $status = filter_input(INPUT_POST, 'status');
    $dob = filter_input(INPUT_POST, 'dob');
    $type_id = filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT);
    $image = $_FILES['file1'];   

    require_once('database.php');
    require_once('image_util.php');

    $base_dir = 'images/';

    // Check for duplicate email
    $queryContacts = '
        SELECT firstName, lastName, emailAddress, phoneNumber, status, dob, imageName FROM contacts';

    $statement = $db->prepare($queryContacts);
    $statement->execute();
    $contacts = $statement->fetchAll();
    $statement->closeCursor();

    foreach ($contacts as $contact) {
        if ($email_address == $contact["emailAddress"]) {
            $_SESSION["add_error"] = "Invalid data, Duplicate Email Address. Try again.";
            $url = "error.php";
            header("Location: " . $url);
            die();  
        }
    }

    if ($first_name == null || $last_name == null || $email_address == null ||
        $phone_number == null || $dob == null || $type_id == null) {
            $_SESSION["add_error"] = "Invalid contact data, Check all fields and try again.";
            $url = "error.php";
            header("Location: " . $url);
            die();  
        }

    $image_name = ''; // default empty

    // ******* Image Upload *******

    if ($image && $image['error'] == UPLOAD_ERR_OK) {
        // process new image
        $original_filename = basename($image['name']);
        $upload_path = $base_dir . $original_filename;
        move_uploaded_file($image['tmp_name'], $upload_path);

        process_image($base_dir, $original_filename);

        // save _100 version in DB
        $dot_pos = strpos($original_filename, '.');
        $name_100 = substr($original_filename, 0, $dot_pos) . '_100' . substr($original_filename, $dot_pos);
        $image_name = $name_100;
    }
    else {
        // Use placeholder
        $placeholder = 'placeholder.jpg';
        $placeholder_100 = 'placeholder_100.jpg';
        $placeholder_400 = 'placeholder_400.jpg';

        if (!file_exists($base_dir . $placeholder_100) || !file_exists($base_dir . $placeholder_400)) {
            process_image($base_dir, $placeholder);
        }

        $image_name = $placeholder_100;
    }

    // Add Contact

    $query = 'INSERT INTO contacts (firstName, lastName, emailAddress, phoneNumber, status, dob, typeID, imageName) 
        VALUES (:firstName, :lastName, :emailAddress, :phoneNumber, :status, :dob, :typeID, :imageName)';

    $statement = $db->prepare($query);
    $statement->bindValue(':firstName', $first_name);
    $statement->bindValue(':lastName', $last_name);
    $statement->bindValue(':emailAddress', $email_address);
    $statement->bindValue(':phoneNumber', $phone_number);
    $statement->bindValue(':status', $status);
    $statement->bindValue(':dob', $dob);
    $statement->bindValue(':typeID', $type_id);
    $statement->bindValue(':imageName', $image_name);
    $statement->execute();
    $statement->closeCursor();

    $_SESSION["fullName"] = $first_name . " " . $last_name;
    $url = "add_confirmation.php";
    header("Location: " . $url);
    die();

?>