<?php # - index.php 
// Michael Wolfe
//Midterm
//03.15.2016
$title = 'Completed Quizzes!';
include ('includes/header_lib.html');
require ('../../midterm_spring_2016.php'); // Connect to the db.

// Create the quizzes query
$select = "SELECT name, not_ready, fairly_ready, seem_ready, ready FROM quizzes
ORDER BY quiz_id";
$r = @mysqli_query ($dbc, $select); // Run the query.	
// Count the number of returned rows:
$num = mysqli_num_rows($r);

if ($num > 0)
{ //if it ran OK, display the records
	if ($r) 
	{ // If it ran OK, display the records.

		// Table header.
		echo '<table align="center" cellspacing="3" cellpadding="3" width="75%">
		<tr><td align="left"><b>Quiz Title</b></td>
			<td align="left"><b>Not Ready</b></td>
			<td align="left"><b>Fairly Ready</b></td>
			<td align="left"><b>Seem Ready</b></td>
			<td align="left"><b>Ready</b></td>
			</tr>';
			
		//Fetch and print all the records:
		while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) 
		{
			echo '<tr>
			<td align="left">' . $row['name'] . '</td>
			<td align="left">' . $row['not_ready'] . '</td>
			<td align="left">' . $row['fairly_ready'] . '</td>
			<td align="left">' . $row['seem_ready'] . '</td>
			<td align="left">' . $row['ready'] . '</td>
			</tr>';
		}
	echo '</table>'; // Close the table.
	
	mysqli_free_result ($r); // Free up the resources.	
	} else { //IF no records returned
		echo '<p class="error">There are currently no completed quizzes.</p>';	
	}

} else { // If it did not run OK.

	// Public message:
	echo '<p class="error">The current quiz results could not be retrieved. We apologize for any inconvenience.</p>';
	
	// Debugging message:
	echo '<p>' . mysqli_error($dbc) . '<br /><br />Query: ' . $q . '</p>';
	
} // End of if ($r) IF.

mysqli_close($dbc); // Close the database connection.

include ('includes/footer_lib.html');
?>