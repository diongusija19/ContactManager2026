<?php
    session_start();

    require_once('database.php');

    $contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);

    $first_name = filter_input(INPUT_POST, 'first_name');
    $last_name = filter_input(INPUT_POST, 'last_name');
    $email_address = filter_input(INPUT_POST, 'email_address');
    $phone_number = filter_input(INPUT_POST, 'phone_number');
    $status = filter_input(INPUT_POST, 'status');
    $dob = filter_input(INPUT_POST, 'dob');

    // Check for duplicate email
    $queryContacts = '
        SELECT contactID, firstName, lastName, emailAddress, phoneNumber, status, dob FROM contacts';

    $statement = $db->prepare($queryContacts);
    $statement->execute();
    $contacts = $statement->fetchAll();
    $statement->closeCursor();

    foreach ($contacts as $contact) {
        if ($email_address == $contact["emailAddress"] && $contact_id != $contact["contactID"]) {
            $_SESSION["add_error"] = "Invalid data, Duplicate Email Address. Try again.";
            $url = "error.php";
            header("Location: " . $url);
            die();  
        }
    }

    if ($first_name == null || $last_name == null || $email_address == null ||
        $phone_number == null || $dob == null) {
            $_SESSION["add_error"] = "Invalid contact data, Check all fields and try again.";
            $url = "error.php";
            header("Location: " . $url);
            die();  
        }

    // Update Contact

    $query = '
        UPDATE contacts
        SET firstName = :firstName,
            lastName = :lastName,
            emailAddress = :emailAddress,
            phoneNumber = :phoneNumber,
            status = :status,
            dob = :dob
        WHERE contactID = :contactID
    ';

    $statement = $db->prepare($query);
    $statement->bindValue(':firstName', $first_name);
    $statement->bindValue(':lastName', $last_name);
    $statement->bindValue(':emailAddress', $email_address);
    $statement->bindValue(':phoneNumber', $phone_number);
    $statement->bindValue(':status', $status);
    $statement->bindValue(':dob', $dob);
    $statement->bindValue(':contactID', $contact_id);
    $statement->execute();
    $statement->closeCursor();

    $_SESSION["fullName"] = $first_name . " " . $last_name;
    $url = "update_confirmation.php";
    header("Location: " . $url);
    die();

?>