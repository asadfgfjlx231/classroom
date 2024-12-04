<?php

function htmlHead() {
    echo <<<HTML
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Osztályok</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
HTML;
}

function formDisplay() {
    echo '<h1>Válassz osztályt</h1>';
    echo '<form method="post" class="button-group">';
    for ($i = 0; $i < 6; $i++) {
        echo "<input type='submit' name='button$i' value='" . getKlasse()[$i] . "' />";
    }
    echo '<input type="submit" name="button_all" value="*">';
    echo '<input type="submit" name="show_averages" value="Osztályátlagok">';
    echo '<input type="submit" name="rank" value="Rangsor">';
    echo '<input type="submit" name="avg" value="Átlag">';
    echo '<input type="submit" name="best" value="Legjobbak">';
    echo '</form>';
}

function divStart() {
    echo '<div class="class-names">';
}

function divEnd() {
    echo '</div>';
}

function displayClass($className, $students) {
    echo "<h2>$className</h2>";
    foreach ($students as $student) {
        echo '<div class="student">';
        echo '<h3>' . $student["name"] . ' (' . $student["gender"] . ')</h3>';
        foreach ($student["subjects"] as $subject => $grades) {
            echo '<div class="subject">';
            echo "<span>$subject:</span> " . implode(', ', $grades);
            echo '</div>';
        }
        echo '</div>';
    }
}

function displayClassWithExport($className, $students, $classIndex) {
    displayClass($className, $students);
    echo <<<HTML
    <form method="post">
        <input type="hidden" name="class_index" value="$classIndex">
        <input type="submit" name="export_class" value="Exportálás CSV-be">
    </form>
HTML;
}

function htmlEnd() {
    echo <<<HTML
</div>
</body>
</html>
HTML;
}


function displayAverages($averages) {
    echo '<div class="averages">';
    echo '<h2>Osztályok átlageredményei</h2>';
    foreach ($averages as $className => $subjects) {
        echo "<h3>$className</h3>";
        foreach ($subjects as $subject => $average) {
            echo "<p>$subject: $average</p>";
        }
    }
    $schoolSubjectAverages = calculateSchoolSubjectAverages();
    echo"<h3>Az Összesített tantárgyi átlag</h3>";
foreach ($schoolSubjectAverages as $subject => $average) {
    echo "<p> $subject átlaga: $average.</p>";
}


   
    echo <<<HTML
    <form method="post">
        <input type="submit" name="export_averages" value="Exportálás CSV-be">
    </form>
HTML;

    echo '</div>';
}
function displayRankings($rankings) {
    echo '<div class="rankings">';
    echo '<h2>Osztály rangsorok</h2>';
    
    foreach ($rankings as $className => $subjects) {
        echo "<h3>$className</h3>";
        
       
        foreach ($subjects as $subject => $students) {
            echo "<h4>$subject</h4>";
            echo '<ol>';
            foreach ($students as $rank => $student) {
                echo "<li>" . $student['name'] . " - " . round($student['grade'], 2) . "</li>";
            }
            echo '</ol>';
        }
    }

    echo '</div>';
}
function displaySchoolSubjectRankings($subjectRankings) {
    echo '<div class="school-rankings">';
    echo '<h2>Iskolai Tantárgyak Szerinti Rangsorok</h2>';

    
    foreach ($subjectRankings as $subject => $students) {
        echo "<h3>$subject</h3>";
        echo '<ol>';

       
        foreach ($students as $rank => $student) {
            echo "<li>" . $student['name'] . " (" . $student['class'] . ") - " . round($student['average'], 2) . "</li>";
        }
        echo '</ol>';
    }
    echo <<<HTML
    <form method="post">
        <input type="submit" name="export_rank" value="Exportálás CSV-be">
    </form>
HTML;

    echo '</div>';
}
function displaySubjectAverages($subjectAverages) {
    echo '<div class="subject-averages">';
    echo '<h2>Tanulók Tantárgyi Átlagai</h2>';
    foreach ($subjectAverages as $average) {
        echo "<p>{$average['clasname']}-{$average['name']} - {$average['subject']}: {$average['average']}</p>";
    }
    echo <<<HTML
    <form method="post">
        <input type="submit" name="export_avg" value="Exportálás CSV-be">
    </form>
HTML;
    echo '</div>';
}
function displayBestClasses($result) {
    
    echo '<div class="best-classes">';
    echo '<h2>Legjobb és legrosszabb Osztályok Tantárgyak alpján </h2>';
    
    
    echo '<h3>Összesítésben leggjobb és legrosszabb osztály</h3>';
    echo '<ol>';
    foreach ($result['overallRanking'] as $rank => $class) {
        echo "<li>" . $class['class'] . " - Átlag: " . round($class['average'], 2) . "</li>";
    }
    echo '</ol>';

   
    foreach ($result['subjectRankings'] as $subject => $rankings) {
        echo "<h3>$subject-ben Legjobb és a LEgrosszabb osztály</h3>";
        echo '<ol>';
        foreach ($rankings as $rank => $ranking) {
            
            echo "<li>" . $ranking['class'] . " - Átlag: " . round($ranking['average'], 2) . "</li>";
            
        }
        echo '</ol>';
    }
    echo <<<HTML
    <form method="post">
        <input type="submit" name="export_best" value="Exportálás CSV-be">
    </form>
HTML;

    echo '</div>';
}
function displayAllAvarage()
{
    $schoolSubjectAverages = calculateSchoolSubjectAverages();
    echo"<h3>Az Összesített tantárgyi átlag</h3>";
foreach ($schoolSubjectAverages as $subject => $average) {
    echo " $subject átlaga: $average.\n";
}

}
function displayStudentAveragesByClass() {
    $studentAverages = calculateStudentAveragesByClass(); 

   
    foreach ($studentAverages as $className => $students) {
        echo"<ol>";
        echo "<p>Osztály: $className</p>";
       

        if (empty($students)) {
            echo "<p>Nincs adat a tanulókról.</p>";
        } else {
            foreach ($students as $student) {
                echo "<li>{$student['name']}, Átlag: {$student['average']}</li>";
            }
        }

        echo "    "; 
        echo"</ol>";
    }
}


