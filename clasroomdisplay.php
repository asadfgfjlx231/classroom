<?php 
require_once "classroom.php";
require_once "classroomhtml.php";

$selectedClass = null;
$classStudents = [];
$averages = [];
$schoolAverage = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["button_all"])) {
        $allClasses = getKlasse();
        foreach ($allClasses as $index => $className) {
            $classStudents[$className] = loadClassNamesWithSubjects($index);
        }
        $selectedClass = '*';
    } elseif (isset($_POST["show_averages"])) {
        list($averages, $schoolAverage) = calculateClassAverages();
    }  elseif (isset($_POST['export_class'])) {
        $classIndex = $_POST['class_index'];
        $className = getKlasse()[$classIndex];
        $students = loadClassNamesWithSubjects($classIndex);
        exportClassToCSV($className, $students);
        echo "<p>Az osztály sikeresen exportálva: $className</p>";
     } elseif (isset($_POST["export_averages"])) {
        exportClassAveragesToCsv();
        $subjectAverages=calculateSchoolSubjectAverages();
        saveOverAllAveragesToCSV($subjectAverages); 
        echo "<p>Az osztály sikeresen exportálva:</p>";
    }  elseif (isset($_POST["export_rank"])) {
        exportTopClassRankingsToCsv(); 
        exportSchoolSubjectRankingsToCsv(); 
        echo "<p>Az osztály sikeresen exportálva:</p>";
    } elseif (isset($_POST["export_avg"])) {
        saveSubjectAveragesToCsv();

        echo "<p>Az osztály sikeresen exportálva:</p>";
    } elseif (isset($_POST["export_best"])) {
        $result = calculateAndRankClasses(); 
        exportOverallRankingToCSV($result);
        exportSubjectRankingsToCSV($result);
        echo "<p>Az osztály sikeresen exportálva:</p>";
    } 

   
    
    else {
        for ($i = 0; $i < 6; $i++) {
            if (isset($_POST["button$i"])) {
                $selectedClass = getKlasse()[$i];
                $classStudents = loadClassNamesWithSubjects($i);
            }
        }
    }
}

htmlHead();
formDisplay();

if ($selectedClass) {
    divStart();
    if ($selectedClass === '*') {
        foreach ($classStudents as $className => $students) {
            displayClass($className, $students);
        }
    } else {
        $classIndex = array_search($selectedClass, getKlasse());
        displayClassWithExport($selectedClass, $classStudents, $classIndex);
    }
    divEnd();
} elseif (!empty($averages)) {
    displayAverages($averages);
    
}
elseif (isset($_POST['rank'])) {
    $rankings = generateTopClassRankings();
    $rankingData = calculateAndRankClasses();
    $subjectRankings = generateSchoolSubjectRankings();
    displayRankings($rankings);
    displaySchoolSubjectRankings($subjectRankings);
    displayStudentAveragesByClass();

}
elseif (isset($_POST["show_averages"])) {
    displayAllAvarage();
    
} 
elseif (isset($_POST["avg"])) {
    $subjectAverages = calculateSubjectAveragesForAllStudents();  
    displaySubjectAverages($subjectAverages);
}
elseif (isset($_POST['best'])) {
    $result = calculateAndRankClasses(); 
    displayBestClasses($result); 
}



htmlEnd();
