<?php

// Page header:
require ('../../midterm_spring_2016.php'); // Connect to the db.

$title = "SELECT name, description FROM quizzes 
          WHERE quiz_id = 1";
		 
$q_title = @mysqli_query ($dbc, $title);  // Run the query.

if ($q_title) 
   {
    $row = mysqli_fetch_array ($q_title, MYSQLI_ASSOC);
	echo '<h2>'. $row["name"]. '</h2>';
	echo '<h3>'. $row["description"]. '</h3>'; // Call title and description.
   }		 

// Do query to get questions for a quiz
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	//print_r($_POST);
	
	$errors = array(); // initialize an error array.
	$response = array(); // initialize an incorrect array.

	// Check for a first name:
	if (empty($_POST['first_name'])) {
		$errors[] = '<p>You forgot to enter your first name.</p>';
	} else {
		$fn = mysqli_real_escape_string($dbc, trim($_POST['first_name']));
	}
	
	// Check for a last name:
	if (empty($_POST['last_name'])) {
		$errors[] = '<p>You forgot to enter your last name.</p>';
	} else {
		$ln = mysqli_real_escape_string($dbc, trim($_POST['last_name']));
	}

	// Check for an email address:
	if (empty($_POST['email'])) {
		$errors[] = '<p>You forgot to enter your email address.</p>';
	} else {
		$e = mysqli_real_escape_string($dbc, trim($_POST['email']));
	}

	$answers = "SELECT question, question_id, feedback, answer FROM questions
           JOIN answers USING (question_id)
		   WHERE quiz_id = 1 AND correct_ans_id = answer_id";

	$q_answers = @mysqli_query ($dbc, $answers);  // Run the query.	

	$answer_count = 0;

	$num = mysqli_num_rows($q_answers);

	while ($row = mysqli_fetch_array($q_answers, MYSQLI_ASSOC)) 
	{
		//echo "<p>". $row["question_id"] . "  " . $row["answer"]. "</p>";
		$id = $row['question_id'];
		$feedback = $row['feedback'];
		$question = $row['question'];
		
		if (!isset($_POST[$id]))
		{
			$errors[] = '<p class="error">You forgot to answer question ' . $id . '.</p>';
		}		
		else
		{
			if ($_POST[$id] == $row['answer'])
			{
			    $response[] = '<p>' . $id . ". " . $question . '</p><p>CORRECT! </p>';
				$answer_count++;
			}
			else
			{
				$response[] = '<p>' . $id . ". " . $question . '</p><p>INCORRECT! ' . $feedback . '</p>';
			}
		}
	} 
		if (empty($errors))
		{
			foreach ($response as $m)
			{
				echo "$m\n";
			}
			
			$score = ($answer_count / $num) * 100;

            echo "<p> Thank you, $fn $ln, for taking the quiz. </p>";

            echo "<br />You answered " . $answer_count . " out of &nbsp" . $num . " questions correct for a score of " . $score . "%.<br />";
	
			if ($score <= 70) 
			{
				echo "Online learning is not for you. Please sign up for a traditional course.";
			}
			elseif ($score <= 75)
			{
				echo "You may be fine, but it's best if you stick with traditional courses for now.";
			}
			elseif ($score <= 85)
			{
				echo "You seem fairly ready for online learning.";
			}
			elseif ($score <= 95)
			{
				echo "You seem ready for online learning.";
			}
			elseif ($score <= 100)
			{
				echo "You are ready for online learning. Sign up!";
			}
		}
		else
		{
			echo '<h3> Error! </h3>
			<p class="error"> The following error(s) occurred: <br />';
			foreach ($errors as $msg)
			{
				echo "$msg\n";
			}
		}
}
else
{
	$q = "SELECT quiz_id, question, question_id, correct_ans_id
		 FROM questions WHERE quiz_id = 1"; // ORDER BY RAND()";
	$r = @mysqli_query ($dbc, $q);  // Run the query.
?>

<form action="#" method="post">
	<p>First Name: <input type="text" name="first_name" size="15" maxlength="20" value="<?php if (isset($_POST['first_name'])) echo $_POST['first_name']; ?>" required="required" /></p>
	<p>Last Name: <input type="text" name="last_name" size="15" maxlength="40" value="<?php if (isset($_POST['last_name'])) echo $_POST['last_name']; ?>" required="required" /></p>
	<p>Email Address: <input type="text" name="email" size="20" maxlength="60" value="<?php if (isset($_POST['email'])) echo $_POST['email']; ?>" required="required" /></p>
	
<?php

		echo '<table cellspacing="3" cellpadding="3" width="75%">
	';

	// Display question

	// variable declaration
	$count = 1;
	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) 
	{ 
		echo "<tr><td>$count. " . $row['question'] . '</td></tr>';
		$count++;
		
		$a = "SELECT answer, answer_id FROM answers WHERE question_id = ". $row['question_id'];
		$result = @mysqli_query ($dbc, $a);  // Run the query.
		
			while ($answer_row = mysqli_fetch_array($result, MYSQLI_ASSOC)) 
			{ 
			echo "<tr><td>";
			create_radio($row['question_id'], $answer_row['answer']);
			echo '</td></tr>';
			}
	}	

	echo '</table><input type="Submit" name="Submit" value="Submit"></form>';
}

function create_radio($name, $value) 
{
	// Start the element:
	echo '<input type="radio" name='. $name. ' value="'. $value . '"';
	echo ' required="required" ';
	// Check for stickiness:
	if (isset($_POST[$name]) && ($_POST[$name] == $value)) 
	{
		echo ' checked="checked"';
	}
	
	// Complete the element:
	echo " /> $value ";
 } 
 
?>