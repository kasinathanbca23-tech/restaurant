<?php
// 1. START THE SESSION and Initialization
// Sessions are used to store game state between page loads (turns)
session_start();

// Initialize the score if it hasn't been set yet
if (!isset($_SESSION['score'])) {
    $_SESSION['score'] = 0;
    $_SESSION['message'] = "Welcome! Click 'Roll the Die' to start.";
}

$roll_result = null;
$game_over = false;

// 2. HANDLE PLAYER ACTION (Form Submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['roll'])) {
    
    // Generate a random number between 1 and 6
    $roll_result = rand(1, 6); 

    if ($roll_result === 1) {
        // Lose condition: Player rolled a 1
        $game_over = true;
        $_SESSION['score'] = 0;
        $_SESSION['message'] = "Ouch! You rolled a **1**. Your total score is reset to 0. Game Over!";

    } else {
        // Success condition: Add the roll to the score
        $_SESSION['score'] += $roll_result;
        
        $_SESSION['message'] = "You rolled a **$roll_result**! Your current score is now **{$_SESSION['score']}**.";

        // Win condition: Player reaches 25 points
        if ($_SESSION['score'] >= 25) {
            $game_over = true;
            $_SESSION['message'] = "🎉 **CONGRATULATIONS!** You reached 25 points and won the game!";
        }
    }
}

// 3. HANDLE RESET ACTION
if (isset($_POST['reset'])) {
    session_destroy();
    // Redirect to clear POST data and restart cleanly
    header("Location: index.php");
    exit;
}

// Prepare the display message
$display_message = $_SESSION['message'];
$current_score = $_SESSION['score'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple PHP Dice Game</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding-top: 50px; }
        .container { max-width: 400px; margin: 0 auto; padding: 20px; border: 2px solid #333; border-radius: 10px; }
        h1 { color: #007bff; }
        .score { font-size: 2em; margin: 20px 0; font-weight: bold; }
        .message { margin: 15px 0; padding: 10px; background-color: #f0f0f0; border-radius: 5px; }
        button { padding: 10px 20px; font-size: 1.1em; cursor: pointer; }
        .win { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .loss { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <h1>🎲 The Dice Accumulator Game 🎲</h1>
    <p>Goal: Reach a score of <strong>25 points</strong>.</p>
    <p>Warning: If you roll a **1**, your entire score is reset to zero and the game ends!</p>

    <hr>
    
    <?php if ($game_over): ?>
        <div class="message <?php echo ($current_score >= 25 ? 'win' : 'loss'); ?>">
            <?php echo $display_message; ?>
        </div>
        
        <form method="POST" action="index.php">
            <button type="submit" name="reset">Start New Game</button>
        </form>

    <?php else: ?>
        
        <div class="message">
            <?php echo $display_message; ?>
        </div>
        
        <div class="score">
            Current Score: <?php echo $current_score; ?>
        </div>
        
        <form method="POST" action="index.php">
            <button type="submit" name="roll">Roll the Die</button>
        </form>
        
    <?php endif; ?>

</div>

</body>
</html>