<?php

//util.php
function flashMessages()
{
    if(isset($_SESSION["success"]))
    {
    	echo('<p style="color:green;">' . $_SESSION["success"] . "</p>\n");
    	unset($_SESSION["success"]);
    }
    if(isset($_SESSION["error"]))
    {
    	echo('<p style="color:red;">' . $_SESSION["error"] . "</p>\n");
    	unset($_SESSION["error"]);
    }
}


function validateProfile($firstName, $lastName, $email, $headline, $summary)
{
    if(strlen($firstName) < 1 || strlen($lastName) < 1 || strlen($email) < 1 || strlen($headline) < 1 || strlen($summary) < 1)
    {
        $_SESSION["error"] = "All fields are required";
        return false;
    }
    if(strpos($email, "@") === false)
    {
        $_SESSION["error"] = "Email address must contain @";
        return false;
    }

    return true;
}

//*
function validatePos()
{
    for($i = 1; $i <= 9; $i++)
    {
        $year = "year" . $i;
        $desc = "desc" . $i;

        if(isset($_SESSION[$year]) && isset($_SESSION[$desc]))
        {
            $yearVal = $_SESSION[$year];
            $descVal = $_SESSION[$desc];

            if(strlen($yearVal) == 0 || strlen($descVal) == 0)
            {
                $_SESSION["error"] = "All fields are required";
                return false;
            }

            if(!is_numeric($yearVal))
            {
                $_SESSION["error"] = "Position year must be numeric";
                return false;
            }

        }
    }

    return true;
}
//*/

function validateFields($yearField, $otherField, $posOrEdu)
{

    for($i = 1; $i <= 9; $i++)
    {
        $year = $yearField . $i;
        $other = $otherField . $i;


        if(isset($_SESSION[$year]) && isset($_SESSION[$other]))
        {
            $yearVal = $_SESSION[$year];
            $otherVal = $_SESSION[$other];

            if(strlen($yearVal) == 0 || strlen($otherVal) == 0)
            {
                $_SESSION["error"] = "All fields are required";
                return false;
            }

            if(!is_numeric($yearVal))
            {
                $_SESSION["error"] = $posOrEdu . "year must be numeric";
                return false;
            }

        }
    }

    return true;
}

function loadEduOrPos($pdo, $profileID, $eduOrPos)
{
    $sql = "SELECT * FROM " . $eduOrPos . " WHERE profile_id = :prof ORDER BY rank";
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute(array(":prof" => $profileID));
    $positions = array();

    while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
        $positions[] = $row;

    return $positions;
}

function initSession($yearName, $otherName)
{

    for($i = 1; $i <=9; $i++)
    {
        $year = $yearName . $i;
        $other = $otherName . $i;

        if(isset($_POST[$year]) && isset($_POST[$other]))
        {
            $_SESSION[$year] = $_POST[$year];
            $_SESSION[$other] = $_POST[$other];
        }
    }
}

function insertPosition($pdo, $profileID)
{
    $rank = 1;

    for($i = 1; $i <= 9; $i++)
    {
        $year = "year" . $i;
        $desc = "desc" . $i;

        if(isset($_SESSION[$year]) && isset($_SESSION[$desc]))
        {
            $yearVal = $_SESSION[$year];
            $descVal = $_SESSION[$desc];
            unset($_SESSION[$year]);
            unset($_SESSION[$desc]);

            $sql = "INSERT INTO position (profile_id, rank, year, description) VALUES (:pid, :rank, :year, :descr)";
            $stmt = $pdo -> prepare($sql);
            $stmt -> execute(array(":pid" => $profileID, ":rank" => $rank, ":year" => $yearVal, ":descr" => $descVal));
        }
        $rank++;
    }
}

function insertEducation($pdo, $profileID)
{
    $rank = 1;

    for($i = 1; $i <= 9; $i++)
    {
        $year = "edu_year" . $i;
        $school = "edu_school" . $i;

        if(isset($_SESSION[$year]) && isset($_SESSION[$school]))
        {
            $yearVal = $_SESSION[$year];
            $schoolVal = $_SESSION[$school];
            unset($_SESSION[$year]);
            unset($_SESSION[$school]);

            // Try to insert new school in case it is not already in the database

            $institutionID = -1;

            try
            {
                $sql = "INSERT INTO institution(name) VALUES (:schoolName)";
                $stmt = $pdo -> prepare($sql);
                $stmt -> execute(array(":schoolName" => $schoolVal));
                $institutionID = $pdo -> lastInsertId();
            }
            catch(Exception $e)
            {
                $sql = "SELECT institution_id FROM institution WHERE name = :schoolName";
                $stmt = $pdo -> prepare($sql);
                $stmt -> execute(array(":schoolName" => $schoolVal));
                $row = $stmt -> fetch(PDO::FETCH_ASSOC);
                $institutionID = $row["institution_id"];
            }

            $sql = "INSERT INTO education (profile_id, institution_id, rank, year) VALUES (:pid, :iid, :rank, :year)";
            $stmt = $pdo -> prepare($sql);
            $stmt -> execute(array(":pid" => $profileID, ":iid" => $institutionID, ":rank" => $rank, ":year" => $yearVal));

        }
        $rank++;
    }
}

?>
<?php

require_once "pdo.php";
//require_once "util.php";

session_start();

if(!isset($_SESSION["name"]))
	die("ACCESS DENIED");

if(isset($_POST["cancel"]))
{
	// Redirect to index.php
	header("Location: index.php");
	return;
}

// Check to see if we have some POST data, if we do , store it in SESSION
if(isset($_POST["first_name"]) && isset($_POST["last_name"]) && isset($_POST["email"]) && isset($_POST["headline"])
	&& isset($_POST["summary"]))
{
	$_SESSION["first_name"] = $_POST["first_name"];
	$_SESSION["last_name"] = $_POST["last_name"];
	$_SESSION["email"] = $_POST["email"];
	$_SESSION["headline"] = $_POST["headline"];
	$_SESSION["summary"] = $_POST["summary"];

	initSession("year", "desc");
	initSession("edu_year", "edu_school");

	header("Location: add.php");
	return;
}

if(isset($_SESSION["first_name"]) && isset($_SESSION["last_name"]) && isset($_SESSION["email"]) && isset($_SESSION["headline"])
        && isset($_SESSION["summary"]))
{
	$firstName = $_SESSION["first_name"];
    $lastName = $_SESSION["last_name"];
    $email = $_SESSION["email"];
    $headline = $_SESSION["headline"];
    $summary = $_SESSION["summary"];
    unset($_SESSION["first_name"]);
    unset($_SESSION["last_name"]);
    unset($_SESSION["email"]);
    unset($_SESSION["headline"]);
    unset($_SESSION["summary"]);

    //*
	if(validateProfile($firstName, $lastName, $email, $headline, $summary) === true
		&&  validateFields("edu_year", "edu_school", "Education") === true && validateFields("year", "desc", "Position") === true)
	{
		$sql = "INSERT INTO profile (user_id, first_name, last_name, email, headline, summary) VALUES (:uid, :fn, :ln, :em, :he, :su)";
		$stmt = $pdo -> prepare($sql);
		$stmt -> execute(array(":uid" => $_SESSION["user_id"], ":fn" => $firstName, ":ln" => $lastName, ":em" => $email,
			":he" => $headline, ":su" => $summary));

		$profileID = $pdo -> lastInsertId();

		// Insert the position entries
		insertPosition($pdo, $profileID);

		//Insert the education entries
		insertEducation($pdo, $profileID);

		$_SESSION["success"] = "Profile added";

		header("Location: index.php");
		return;
	}
	//*/
}

?>

<!DOCTYPE html>

<html lang = "en">

	<head>
		<meta charset = "utf-8">
		<title>Jared Best | Add Page</title>
		<?php require_once "head.php" ?>
	</head>

	<body>
		<div class = "container">
			<h1>Adding Profile for <?php echo(htmlentities($_SESSION["name"])); ?></h1>
			<?php flashmessages(); ?>
			<form method="post">
				<p>
					First Name :
					<input type="text" name="first_name" size = "60">
				</p>
				<p>
					Last Name :
					<input type="text" name="last_name" size = "60">
				</p>
				<p>
					Email :
					<input type="text" name="email" size = "30">
				</p>
				<p>
					Headline :
					<input type="text" name="headline" size = "80">
				</p>
				<p>
					Summary :<br>
					<textarea name="summary" rows = "8" cols = "80"></textarea>
				</p>
				<p>
					Education:
					<input type="submit" id="addEdu" value="+">
					<div id="edu_fields">
					</div>
				</p>
				<p>
					Position:
					<input type = "submit" id = "addPos" value="+">
					<div id="position_fields">
					</div>
				</p>
				<input type="submit" value = "Add">
				<input type="submit" name="cancel" value = "Cancel">
			</form>
			<script type="text/javascript">

				countPos = 0;
				countEdu = 0;

				$(document).ready(
					function()
					{
						window.console && console.log("Document ready called");

						$("#addPos").click(
							function(event)
							{
								event.preventDefault();
								if(countPos >= 9)
								{
									alert("Maximum of nine position entries exceeded");
									return;
								}

								countPos++;
								window.console && console.log("Adding position" + countPos);

								$("#position_fields").append(
									'<div id="position' + countPos + '">  \
										<p>  \
										    Year :  \
										    <input type = "text" name="year' + countPos + '" value = "" /> \
										    <input type="button" value="-"  \
										        onclick="$(\'#position' + countPos + '\').remove(); return false;">  \
										</p>  \
										<textarea name="desc' + countPos + '" rows="8" cols="80"></textarea>  \
									</div>'
								);

							}
						);

						$("#addEdu").click(
							function(event)
							{
								event.preventDefault();
								if(countEdu >= 9)
								{
									alert("Maximum of nine educastion entries exceeded");
									return;
								}

								countEdu++;
								window.console && console.log("Adding education" + countEdu);

								$("#edu_fields").append(
									'<div id="edu' + countEdu + '">  \
										<p>  \
										    Year :  \
										    <input type = "text" name="edu_year' + countEdu + '" value = "" /> \
										    <input type="button" value="-"  \
										        onclick="$(\'#edu' + countEdu + '\').remove(); return false;">  \
										</p>  \
										<p>   \
											School :    \
											<input type = "text" size="80" name="edu_school' + countEdu + '" class="school" value="">   \
										</p>   \
									</div>'
								);

								$(".school").autocomplete(
									{source: "school.php"}
								);
							}
						);

					}
				);
			</script>
		</div>
	</body>

</html>
