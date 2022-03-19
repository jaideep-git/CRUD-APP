<?php

// * connect to the common file that contains the MySQL credentials

require_once("./inc/connect_pdo.php");
$id = $_POST["id"];
$name_last = $_POST["name_last"];
$name_first = $_POST["name_first"];
$email_dc = $_POST["email_dc"];
$email_other = $_POST["email_other"];
$phone = $_POST["phone"];
$program_id = $_POST["program_id"];
$program_year = $_POST["program_year"];
$agreement_signed = $_POST["agreement_signed"];
$strikes = $_POST["strikes"];
$submit = $_POST["submit"];
$add = $_POST["add"];
$download = $_POST["download"];

// * update insert and delete command

if ($_SERVER["REQUEST_METHOD"] == "GET"){
    //delete things
    if (!empty($_GET["delete"])){
        $delete_id = $_GET["delete"];
        $query = "DELETE FROM borrower WHERE borrower_id = $delete_id";
        $dbo->query($query);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    // check if the download was pressed
    if ($download){
        // run the excel script
        include_once("./excel.php");
    }

    if ($submit == "Update") {
        
        foreach ($name_last as $key=>$value) {
            // sanitize the data
            $name_last[$key] = addslashes($name_last[$key]);  
            $name_first[$key] = addslashes($name_first[$key]); 
            $email_dc[$key] = addslashes($email_dc[$key]); 
            $email_other[$key] = addslashes($email_other[$key]); 
            $phone[$key] = addslashes($phone[$key]);  
            $program_id[$key] = addslashes($program_id[$key]); 
            $program_year[$key] = addslashes($program_year[$key]); 
            $agreement_signed[$key] = addslashes($agreement_signed[$key]); 
            $strikes[$key] = addslashes($strikes[$key]);  
            
            //are we adding a new person??
            if ($key == -1){
                $query ="INSERT INTO borrower
                    SET name_last = '$name_last[$key]',
                    name_first = '$name_first[$key]',
                    email_dc = '$email_dc[$key]',
                    email_other = '$email_other[$key]',
                    phone = '$phone[$key]',
                    program_id = '$program_id[$key]',
                    program_year = '$program_year[$key]',
                    agreement_signed = '$agreement_signed[$key]',
                    strikes = '$strikes[$key]' ";
                $dbo->query($query);
                $id = $dbo->lastInsertId();
            } else {
                $query = "UPDATE borrower
                    SET name_last = '$name_last[$key]',
                    name_first = '$name_first[$key]',
                    email_dc = '$email_dc[$key]',
                    email_other = '$email_other[$key]',
                    phone = '$phone[$key]',
                    program_id = '$program_id[$key]',
                    program_year = '$program_year[$key]',
                    agreement_signed = '$agreement_signed[$key]',
                    strikes = '$strikes[$key]' 
                WHERE borrower_id = '$key' ";
                $dbo->query($query);
                $id = $key;
            } 
            // IF images were submitted to the form
            if (!empty($_FILES["image"]["name"][$key])){
                //store needed info in variables
                $fileName = $_FILES["image"]["name"][$key];
                $fileSize = $_FILES["image"]["size"][$key];
                $extension = pathinfo($_FILES["image"]["name"][$key], PATHINFO_EXTENSION);
                $fileTmp_name = $_FILES["image"]["tmp_name"][$key];
                // delete previously uploaded image
                // query the database for the old image name
                $query = "SELECT image
                    FROM borrower
                    WHERE borrower_id = '$id'";
                foreach($dbo->query($query) as $row){
                    $old_image = stripslashes($row[0]);
                }
                $old_image_path = "./images/$id/$old_image";
                // IF old image exists, delete it from server
                if (file_exists($old_image_path)){
                    unlink($old_image_path);
                }
                //Clean up the image filename
                $fileName = preg_replace('/^-+|-+$/', '', strtolower(preg_replace('/[^a-zA-Z0-9.]+/', '_', $fileName)));
                // Create DIR (if needed)
                @mkdir("./images/$id", 0777);
                // Move file from tmp to new DIR
                $sourcePath = $_FILES["image"]["tmp_name"][$key];
                $targetPath = "./images/$id/$fileName";

                //Only upload if image
                if ($extension == "jpg" || $extension == "jpeg" || $extension == "png"){
                    //Only upload if < 2MB
                    if ($fileSize < 2000000){
                        //ALL GOOD LETS UPLOAD!
                        if (move_uploaded_file($sourcePath, $targetPath)){
                            //move successful, insert image name into database
                            $query = "UPDATE borrower
                                SET image = '$fileName'
                                WHERE borrower_id = '$id' ";
                            $dbo->query($query);
                        }
                    }else{
                        $errMsg = "File TOO BIG!";
                    } //end if 2MB
                }else{
                    $errMsg = "Not correct image type!";
                } //end if image
            }
        }
        
    }
}

// connect to the MySQL database and get information
// at the sametime parse through the data(loop through and get)

$query = "SELECT id, name
FROM programs
ORDER BY name";

foreach($dbo->query($query) as $row) {
    // assign the data to individual variables
    $program_id = stripslashes($row[0]);
    $name = stripslashes($row[1]);
    $programs_arr[$program_id] = $name;   
}

// * Form Interface

echo ("<!doctype html>
<html class=\"no-js\" lang=\"en\">

<head>
<meta charset=\"utf-8\" />
<meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\">
<title>Recursive Form From MySQL</title>
<link rel=\"stylesheet\" href=\"css/materialize.css\">
<script src=\"https://kit.fontawesome.com/0e2713cbb5.js\" crossorigin=\"anonymous\"></script>
<link rel=\"stylesheet\" href=\"css/main.css\">
</head>

<body>

<header>
<h1>Student Management</h1>
</header>

<main class=\"row\">

<article class=\"col l12\">

    <form method=\"post\" action=\"./index.php\" enctype=\"multipart/form-data\">
        <input type=\"submit\" class=\"submit\" name=\"submit\" id=\"submit\" value=\"Update\">
        <input type=\"submit\" class=\"submit\" name=\"add\" id=\"add\" value=\"Add Person\">
        <input type=\"submit\" class=\"submit\" name=\"cancel\" id=\"cancel\" value=\"Cancel\">
        <input type=\"submit\" class=\"submit\" name=\"download\" id=\"download\" value=\"Download Excel Spreadsheet\">

    <table>
        <tr valign=\"bottom\">
            <th>Last Name</th>
            <th>First Name</th>
            <th> <i class=\"fas fa-image\"></i> Image</th>
            <th>DC Email</th>
            <th>Phone Number</th>
            <th>Program ID</th>
            <th>Program Year<br>
            1st/2nd/3rd</th>
            <th>Agreement Signed</th>
        </tr>
    ");

// will NOT go into it if false 0 or empty string ""
if ($add){
    
    $borrower_id = -1;
    $name_last = "";
    $name_first = "";
    $email_dc = "";
    $email_other = "";
    $phone = "";
    $program_id = "28";
    $program_year = 1;
    $agreement_signed = 0;
    $strikes = "0";

    if($agreement_signed){
        $agreement_signed = "checked=\"checked\"";
    }else{
        $agreement_signed ="";
    }

    unset($program_1, $program_2, $program_3);

    switch ($program_year){
        case 1:
            $program_1 = " checked=\"checked\"";
            break;
        case 2:
            $program_2 = " checked=\"checked\"";
            break;
        case 3:
            $program_3 = " checked=\"checked\"";
            break;
        default:
            $program_1 = " checked=\"checked\"";
            break;
    }
    echo(   "<tr class=\"new-borrower\">
                <th colspan=\"7\">New Borrower</th>
            </tr>
    
            <tr class=\"new-borrower\">
                <td>
                    <input type=\"text\" name=\"name_last[$borrower_id]\" id=\"name_last_$borrower_id\" value=\"$name_last\" placeholder=\"Last Name\" maxlength=\"50\">
                </td>

                <td>
                    <input type=\"text\" name=\"name_first[$borrower_id]\" id=\"name_first_$borrower_id\" value=\"$name_first\" placeholder=\"First Name\" maxlength=\"50\">
                </td>

                <td>
                <input type=\"file\" name=\"image[$borrower_id]\" id=\"image_$borrower_id\" accept=\".jpg, .jpeg, .png\">
                </td>
            
                <td>
                    <input type=\"text\" name=\"email_dc[$borrower_id]\" id=\"email_dc_$borrower_id\" value=\"$email_dc\" placeholder=\"Email DC\" maxlength=\"150\">
                </td>
            
                <td>
                    <input type=\"text\" name=\"phone[$borrower_id]\" id=\"phone_$borrower_id\" value=\"$phone\" placeholder=\"Phone Number\" maxlength=\"20\">
                </td>

                <td>
                    <select name=\"program_id[$borrower_id]\">
        ");
                
                foreach($programs_arr as $key=>$value) {
                    if ($program_id == $key) {
                        $selected = " selected=\"selected\"";
                    } else {
                        $selected = "";
                    }
                    
                    echo("<option value=\"$key\"$selected>$value</option>");
                }
                
            echo("</select>
                </td>
            
                <td class=\"program-year\">
                    <input type=\"radio\"   name=\"program_year[$borrower_id]\" id=\"program_1_$borrower_id\" value=\"1\"$program_1> |
                    <input type=\"radio\"  name=\"program_year[$borrower_id]\" id=\"program_2_$borrower_id\" value=\"2\"$program_2> |
                    <input type=\"radio\"  name=\"program_year[$borrower_id]\" id=\"program_3_$borrower_id\" value=\"3\"$program_3>
                </td>

                
                <td align=\"left\">
                    <input type=\"checkbox\" name=\"agreement_signed[$borrower_id]\" id=\"agreement_signed_$borrower_id\" value=\"1\"$checked>
                </td>
            
                <td></td> <!--empty cell for looks-->
            </tr>
        ");

}

// create a SQL command to use below to get information
$query = "SELECT borrower_id, name_last, name_first, email_dc, email_other, phone, program_id, program_year, agreement_signed, strikes, image
FROM borrower
ORDER BY name_last";

// connect to the MySQL database and get information
// at the sametime parse through the data(loop through and get)

foreach($dbo->query($query) as $row) {

    // assign the data to individual variables
    $borrower_id = stripslashes($row[0]);
    $name_last = stripslashes($row[1]);
    $name_first = stripslashes($row[2]);
    $email_dc = stripslashes($row[3]);
    $email_other = stripslashes($row[4]);
    $phone = stripslashes($row[5]);
    $program_id = stripslashes($row[6]);
    $program_year = stripslashes($row[7]);
    $agreement_signed = stripslashes($row[8]);
    $strikes = stripslashes($row[9]);
    $image = stripslashes($row[10]);

    if ($image){
        $img = "<br>
                <img height=\"50px\" class=\"mainImage\" src=\"./images/$borrower_id/$image\" alt=\"$name\">
                ";
    }else{
        $img = "";
    }


    if($agreement_signed){
        $checked = "checked=\"checked\"";
    }else{
        $checked ="";
    }
    
    unset($program_1);
    unset($program_2);
    unset($program_3);
    
    unset($program_1, $program_2, $program_3);
    
    switch($program_year) {
        case 1 :
            $program_1 = " checked=\"checked\"";
            break;
        case 2 :
            $program_2 = " checked=\"checked\"";
            break;
        case 3 :
            $program_3 = " checked=\"checked\"";
            break;
        default :
            $program_1 = " checked=\"checked\"";
            break;
    }
    
    echo("<tr>
            <td>
                <input type=\"text\" name=\"name_last[$borrower_id]\" id=\"name_last_$borrower_id\" value=\"$name_last\" placeholder=\"Last Name\" maxlength=\"50\">
            </td>
        
            <td>
                <input type=\"text\" name=\"name_first[$borrower_id]\" id=\"name_first_$borrower_id\" value=\"$name_first\" placeholder=\"First Name\" maxlength=\"50\">
            </td>

            <td>
            <input type=\"file\" name=\"image[$borrower_id]\" id=\"image_$borrower_id\" accept=\".jpg, .jpeg, .png\">
            $img
            </td>
        
            <td>
                <input type=\"text\" name=\"email_dc[$borrower_id]\" id=\"email_dc_$borrower_id\" value=\"$email_dc\" placeholder=\"Email DC\" maxlength=\"150\">
            </td>

            <td>
                <input type=\"text\" name=\"phone[$borrower_id]\" id=\"phone_$borrower_id\" value=\"$phone\" placeholder=\"Phone Number\" maxlength=\"20\">
            </td>

            <td>
            <select name=\"program_id[$borrower_id]\">
    ");
        
        foreach($programs_arr as $key=>$value) {
            if ($program_id == $key) {
                $selected = " selected=\"selected\"";
            } else {
                $selected = "";
            }
            
            echo("<option value=\"$key\"$selected>$value</option>");
        }
        
    echo("</select>
            </td>
            <td>
                <input type=\"radio\" name=\"program_year[$borrower_id]\" id=\"program_1_$borrower_id\" value=\"1\"$program_1> |
                <input type=\"radio\" name=\"program_year[$borrower_id]\" id=\"program_2_$borrower_id\" value=\"2\"$program_2> |
                <input type=\"radio\" name=\"program_year[$borrower_id]\" id=\"program_3_$borrower_id\" value=\"3\"$program_3>
            </td>
            
            
            <td align=\"left\">
                <input type=\"checkbox\" name=\"agreement_signed[$borrower_id]\" id=\"agreement_signed_$borrower_id\" value=\"1\"$checked>
            </td>
    
            <td>
                <a class=\"link\" href=\"?delete=$borrower_id\">Delete</a>
            </td>
    
        </th>
    ");

} 
echo("</table>
</p>

</div>
</form>
</article>
</main>

<footer>
<p>JAIDEEP</p>
</footer>

</body>
</html>
");
?>
