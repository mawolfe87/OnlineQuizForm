<?php

// Page header:
echo '<h1>Midterm</h1>';

require ('../../midterm_spring_2016.php'); // Connect to the db.

// Do query to get questions for a quiz
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	//print_r($_POST);

$answers = "SELECT question_id, answer FROM questions
           JOIN answers USING (question_id)
		   WHERE quiz_id = 1 AND correct_ans_id = answer_id";

$q_answers = @mysqli_query ($dbc, $answers);  // Run the query.	

$answer_count = 0;
$num = mysqli_num_rows($q_answers);
	while ($row = mysqli_fetch_array($q_answers, MYSQLI_ASSOC)) 
	{
		//echo "<p>". $row["question_id"] . "  " . $row["answer"]. "</p>";
		$id = $row['question_id'];
		if (isset($_POST[$id]) && ($_POST[$id] == $row['answer']))
		{
			echo $id . " You answered correctly! <br />";
			$answer_count++;
		}
		else
		{
			echo $id . " Try again, dummy! <br />";
		}
	} 

$score = ($answer_count / $num) * 100;
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
	$q = "SELECT quiz_id, question, question_id, correct_ans_id
		 FROM questions WHERE quiz_id = 1"; // ORDER BY RAND()";
	$r = @mysqli_query ($dbc, $q);  // Run the query.

		echo '<form action="quiz_p.php" method="post"><table cellspacing="3" cellpadding="3" width="75%">
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
	
	// Check for stickiness:
	if (isset($_POST[$name]) && ($_POST[$name] == $value)) 
	{
		echo ' checked="checked"';
	}
	
	// Complete the element:
	echo " /> $value ";
 
 } 
?>