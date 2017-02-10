
<?php

// Page header:
$title = 'Take a Quiz!';
require ('../../midterm_spring_2016.php'); // Connect to the db.
include ('includes/header_lib.html');

$page_title = "SELECT name, description FROM quizzes
          WHERE quiz_id = 1";

$q_title = @mysqli_query ($dbc, $title);  // Run the query.

if ($q_title){
    $row = mysqli_fetch_array ($q_title, MYSQLI_ASSOC);
	echo '<h2>'. $row["name"]. '</h2>';
	echo '<h3>'. $row["description"]. '</h3>'; // Call title and description.
}

// If POSTING form information --->
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

	// Declare array variables
	$errors = array();
	$user_info_errors = array();
	$response = array();

	// Check for a first name:
	if (empty($_POST['first_name'])){
		$user_info_errors[] = 'You forgot to enter your first name.';
	} else {
		$fn = mysqli_real_escape_string($dbc, trim($_POST['first_name']));
	}

	// Check for a last name:
	if (empty($_POST['last_name'])){
		$user_info_errors[] = 'You forgot to enter your last name.';
	} else {
		$ln = mysqli_real_escape_string($dbc, trim($_POST['last_name']));
	}

	// Check for an email address:
	if (empty($_POST['email'])){
		$user_info_errors[] = 'You forgot to enter your email address.';
	} else {
		$e = mysqli_real_escape_string($dbc, trim($_POST['email']));
	}

	// Check if there are no user info errors
	if (empty($user_info_errors)){ //IF ok...
		// Create a variable for the INSERT query for the user information
		// and a variable for the running of the query ($uqr = either true or false)
		$uq = "INSERT INTO users (first_name, last_name, email) VALUES ('$fn', '$ln', '$e')";
		$uqr = @mysqli_query($dbc, $uq);

		// Create a variable for the SELECT query
		$answers = "SELECT question, question_id, feedback, answer FROM questions
				JOIN answers USING (question_id)
				WHERE quiz_id = 1 AND correct_ans_id = answer_id";
		// Run the query
		$q_answers = @mysqli_query ($dbc, $answers);
		// Initialize question and answer count variables
		$answer_count = 0;
		$question_number = 1;
		$num = mysqli_num_rows($q_answers);

		// While loop to process answered questions
		while ($row = mysqli_fetch_array($q_answers, MYSQLI_ASSOC)){
			// Declare shorthand variables (optional)
			$id = $row["question_id"];
			$answer = $row["answer"];
			$feedback = $row["feedback"];
			$question = $row["question"];

			// Radio group answer validation - if NOT set, send error message to errors array
			if(!isset($_POST[$id])){
				$errors[] = '<p class="error">You forgot to enter an  answer for question ' . $id . '.</p>';

      // Else..
			} else {
				//Check if user input ($_POST[$id]) is equal to the answer for each question respectively ($row['answer'])
				if ($_POST[$id] == $row['answer']){

					// If yes - create variable for UPDATE query to count the number of times the question was answered correctly
					// and a variable for the running of that query
					$correct = "UPDATE `questions` SET `answered_correctly` = `answered_correctly` + 1 WHERE question_id = ". $id;
					$q_question_count = @mysqli_query($dbc, $correct);

					$a = "SELECT answer, answer_id FROM answers WHERE question_id = ". $row['question_id'];
					$result = @mysqli_query ($dbc, $a);  // Run the query.

					// Increment the answer count
					$answer_count++;

					// Add correct answer response
					$text = '<div id="correct_response"><p id="question">' . $question_number . ". " . $row['question'] . '</p><br /><ul id="results">';
					while ($answer_row = mysqli_fetch_array($result, MYSQLI_ASSOC)){

						if ($answer_row['answer'] == $_POST[$id]){
							$text .= '<li id="correct">' . $answer_row['answer'] . '</li>';
						} else {
							$text .= '<li id="list">' . $answer_row['answer'] . '</li>';
						}

					}
					$text .= '</ul>';
					$text .='<p id="feedback">' . $feedback . '</p></div>';
					$response[] = $text;
					$question_number++;
				}else{
					$a = "SELECT answer, answer_id FROM answers WHERE question_id = ". $row['question_id'];
					$result = @mysqli_query ($dbc, $a);  // Run the query.
					$text = '<div id="incorrect_response"><p id="question">' . $question_number . ". " . $row['question'] . '</p><br /><ul id="results">';
					while ($answer_row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
						if ($answer_row['answer'] == $_POST[$id]){
							$text .= '<li id="wrong">' . $answer_row['answer'] . '</li>';
						}elseif ($answer_row['answer'] == $row['answer']) {
						$text .= '<li id="right">' . $answer_row['answer'] . '</li>';
						}
						else {
							$text .= '<li id="list">' . $answer_row['answer'] . '</li>';
						}
					}
					$text .= '</ul>';
					$text .='<p id="feedback">' . $feedback . '</p></div>';
					$response[] = $text;
					$question_number++;
				}
			}
		} // END WHILE

		// Check if there are no errors in $errors array
		if (empty($errors)){
			// Calculate score
			$score = ($answer_count / $num) * 100;
			//Display successful quiz submission message and feedback/results
			echo "<p id='prompt'> Thank you, $fn $ln, for taking the quiz.";
			echo "<br />You answered " . $answer_count . " out of &nbsp" . $num . " questions correct for a score of " . $score . "%.";

			if ($score <= 70){
				echo "<br />Online learning is not for you. Please sign up for a traditional course.</p>";
				$count_update = "UPDATE `quizzes` SET `not_ready`= `not_ready` + 1 WHERE quiz_id = 1";
				$q_count = @mysqli_query($dbc, $count_update);
			}
			elseif ($score <= 75){
				echo "<br />You may be fine, but it's best if you stick with traditional courses for now.</p>";
				$count_update = "UPDATE `quizzes` SET `fairly_ready`= `fairly_ready` + 1 WHERE quiz_id = 1";
				$q_count = @mysqli_query($dbc, $count_update);
			}
			elseif ($score <= 85){
				echo "<br />You seem fairly ready for online learning.";
				$count_update = "UPDATE `quizzes` SET `fairly_ready`= `fairly_ready` + 1 WHERE quiz_id = 1</p>";
				$q_count = @mysqli_query($dbc, $count_update);
			}
			elseif ($score <= 95){
				echo "<br />You seem ready for online learning.";
				$count_update = "UPDATE `quizzes` SET `seem_ready`= `seem_ready` + 1 WHERE quiz_id = 1</p>";
				$q_count = @mysqli_query($dbc, $count_update);
			}
			elseif ($score <= 100){
				echo "<br />You are ready for online learning. Sign up!</p>";
				$count_update = "UPDATE `quizzes` SET `ready`= `ready` + 1 WHERE quiz_id = 1";
				$q_count = @mysqli_query($dbc, $count_update);
			}


			foreach ($response as $m){ // Print each response.
				echo "$m\n";
			}

		} else { // Report the errors.

			echo '<h1>Error!</h1>
			<p class="error">The following error(s) occurred:<br />';
			foreach ($errors as $m)
			{ // Print each error.
				echo "$m\n";
			}
			echo '</p><p>Please try again.</p><p><br /></p>';
		}
	} else { // Report the errors.

		echo '<h1>Error!</h1>
		<p class="error">The following error(s) occurred:<br />';
		foreach ($user_info_errors as $msg){ // Print each error.
			echo "$msg\n";
		}
		echo '</p><p>Please try again.</p><p><br /></p>';
	}
// NOT SUBMITTING ANSWERS--->
}else {
	$q = "SELECT quiz_id, question, question_id, correct_ans_id
		 FROM questions WHERE quiz_id = 1 ORDER BY RAND()";
	$r = @mysqli_query ($dbc, $q);  // Run the query.
?>
	<form action="quiz_php.php" method="post">
	<p id='info'>First Name: <input type='text' name='first_name' size='15' maxlength='20' value="<?php if (isset($_POST["first_name"])) echo $_POST["first_name"]; ?>" /></p>
	<p id='info'>Last Name: <input type="text" name="last_name" size="15" maxlength="40" value="<?php if (isset($_POST["last_name"])) echo $_POST["last_name"]; ?>" /></p>
	<p id='info'>Email Address: <input type="text" name="email" size="20" maxlength="60" value="<?php if (isset($_POST["email"])) echo $_POST["email"]; ?>" /></p>
	<br />

<?php

	// Display question
	$count = 1;
	echo '<div id="response">';
	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)){
		echo '<table cellspacing="3" cellpadding="3" width="85%">';
		echo "<p id='question'>$count. " . $row['question'] . '</p>';
		$count++;
		$a = "SELECT answer, answer_id FROM answers WHERE question_id = ". $row['question_id'];
		$result = @mysqli_query ($dbc, $a);  // Run the query.

			while ($answer_row = mysqli_fetch_array($result, MYSQLI_ASSOC))
			{
			echo "<tr><td id='options'>";
			create_radio($row['question_id'], $answer_row['answer']);
			echo '</td></tr>';
			}
	}

	echo '</table><input type="Submit" name="Submit" value="Submit"></form></div>';

}

function create_radio($name, $value){

	// Start the element:
	echo '<input type="radio" name='. $name. ' value="'. $value . '"';

	// Check for stickiness:
	if (isset($_POST[$name]) && ($_POST[$name] == $value)){
		echo ' checked="checked"';
	}

	// Complete the element:
	echo " /> $value ";

 }

 include ('includes/footer_lib.html'); ?>
