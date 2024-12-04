<?php
session_start();
require_once "classroom-data.php";

function getRandom() {
    return rand(10, 15);
}

function getLastnames() {
    return DATA["lastnames"];
}

function getMan() {
    return DATA["firstnames"]["men"];
}

function getWomen() {
    return DATA["firstnames"]["women"];
}

function getName() {
    $nevek = [];
    for ($i = 0; $i < 400; $i++) {
        $Ranveznev = rand(0, 33);
        $Ranman = rand(0, 9);
        $Ranwom = rand(0, 15);
        $veznev = getLastnames()[$Ranveznev];
        $nem = rand(0, 1); 
        $kernev = $nem == 0 ? getMan()[$Ranman] : getWomen()[$Ranwom];
        $nev = $veznev . " " . $kernev;
        $nevek[] = ["name" => $nev, "gender" => $nem == 0 ? "Férfi" : "Nő"];
    }
    return $nevek;
}

function getKlasse() {
    return DATA["classes"];
}

function getSubjects() {
    return DATA["subjects"];
}

function generateGrades() {
    $gradesCount = rand(0, 5); 
    $grades = [];
    for ($i = 0; $i < $gradesCount; $i++) {
        $grades[] = rand(1, 5); 
    }
    return $grades;
}

function loadClassNamesWithSubjects($classIndex) {
    $classKey = "class_$classIndex";
    if (!isset($_SESSION[$classKey])) {
        $letszam = rand(10, 20); 
        $allNames = getName(); 
        $klasse = [];

        for ($i = 0; $i < $letszam; $i++) {
            $randomIndex = rand(0, count($allNames) - 1);
            $student = $allNames[$randomIndex];  
            $nev = $student["name"];
            $gender = $student["gender"];
            
            $student = ["name" => $nev, "gender" => $gender, "subjects" => []];
            foreach (getSubjects() as $subject) {
                $student["subjects"][$subject] = generateGrades();
            }
            $klasse[] = $student;
        }

        $_SESSION[$classKey] = $klasse;
    }
    return $_SESSION[$classKey];
}

function exportClassToCsv($className, $classData) {
   
    $dir = 'export';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

   
    $timestamp = date('Y-m-d_His');
    $filename = $dir . '/' . $className . '-' . $timestamp . '.csv';

    
    $header = ['ID', 'Name', 'Firstname', 'Lastname', 'Gender', 'Alchemy', 'Astrology', 'Biology', 'Chemistry', 'History', 'Informatics', 'Math', 'Physics'];

   
    $file = fopen($filename, 'w');

    
    fputcsv($file, $header);

   
    $id = 1;
    foreach ($classData as $student) {
       
        $nameParts = explode(' ', $student["name"]);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

       
        $row = [
            $id++,              
            $student["name"],   
            $firstName,          
            $lastName,          
            $student["gender"],  
        ];

       
        foreach (getSubjects() as $subject) {
            $grades = isset($student["subjects"][$subject]) ? implode(', ', $student["subjects"][$subject]) : 'Nincs jegy';
            $row[] = $grades;
        }

        
        fputcsv($file, $row,);
    }

    
    fclose($file);
}
function calculateClassAverages() {
    $allClasses = getKlasse();
    $classAverages = [];
    $totalSum = 0;
    $totalCount = 0;

    foreach ($allClasses as $index => $className) {
        $students = loadClassNamesWithSubjects($index);
        $subjectSums = [];
        $subjectCounts = [];
        
        foreach ($students as $student) {
            foreach ($student['subjects'] as $subject => $grades) {
                if (!isset($subjectSums[$subject])) {
                    $subjectSums[$subject] = 0;
                    $subjectCounts[$subject] = 0;
                }
                $subjectSums[$subject] += array_sum($grades);
                $subjectCounts[$subject] += count($grades);
            }
        }

        $averages = [];
        foreach ($subjectSums as $subject => $sum) {
            $average = $subjectCounts[$subject] > 0 ? $sum / $subjectCounts[$subject] : 0;
            $averages[$subject] = round($average, 2);
            $totalSum += $sum;
            $totalCount += $subjectCounts[$subject];
        }
        $classAverages[$className] = $averages;
    }

    $schoolAverage = $totalCount > 0 ? round($totalSum / $totalCount, 2) : 0;

    return [$classAverages, $schoolAverage];
}
function exportClassAveragesToCsv() {

    [$classAverages, $schoolAverage] = calculateClassAverages();


    $dir = 'export';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }


    $timestamp = date('Y-m-d_His');
    $filename = $dir . '/class_averages_' . $timestamp . '.csv';


    $header = ['Class', 'Subject', 'Average'];


    $file = fopen($filename, 'w');


    fputcsv($file, $header);

    foreach ($classAverages as $className => $subjects) {
        foreach ($subjects as $subject => $average) {
            $row = [$className, $subject, $average];
            fputcsv($file, $row);
        }
    }


    fclose($file);


}

function generateTopClassRankings() {
    $allClasses = getKlasse();
    $rankings = [];

   
    foreach ($allClasses as $index => $className) {
        $students = loadClassNamesWithSubjects($index);
        
     
        $subjectRankings = [];

     
        foreach ($students as $student) {
            foreach ($student['subjects'] as $subject => $grades) {
               
                $averageGrade = count($grades) > 0 ? array_sum($grades) / count($grades) : 0;

                
                if (!isset($subjectRankings[$subject])) {
                    $subjectRankings[$subject] = [];
                }

               
                $subjectRankings[$subject][] = [
                    'name' => $student['name'],
                    'grade' => $averageGrade
                ];
            }
        }

        
        foreach ($subjectRankings as $subject => $students) {
            usort($students, function ($a, $b) {
                return $b['grade'] <=> $a['grade']; 
            });
            
            
            $rankings[$className][$subject] = $students;
        }

       
    }

    return $rankings;
}
function generateSchoolSubjectRankings() {
    $allClasses = getKlasse(); 
    $subjectRankings = [];

    
    foreach (getSubjects() as $subject) {
        $subjectStudents = [];

        
        foreach ($allClasses as $index => $className) {
          
            $students = loadClassNamesWithSubjects($index);

           
            foreach ($students as $student) {
              
                if (isset($student['subjects'][$subject])) {
                    $grades = $student['subjects'][$subject];
                    $averageGrade = count($grades) > 0 ? array_sum($grades) / count($grades) : 0;

                 
                    $subjectStudents[] = [
                        'name' => $student['name'],
                        'class' => $className,
                        'average' => $averageGrade
                    ];
                }
            }
        }

        
        usort($subjectStudents, function ($a, $b) {
            return $b['average'] <=> $a['average'];
        });

     
        $subjectRankings[$subject] = $subjectStudents;
    }

    return $subjectRankings;
}
function exportTopClassRankingsToCsv() {
   
    $rankings = generateTopClassRankings();
    
  
    $dir = 'export';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true); 
    }

   
    $timestamp = date('Y-m-d_His');
    $filename = $dir . '/top_class_rankings_' . $timestamp . '.csv';

  
    $file = fopen($filename, 'w');

    $header = ['Class', 'Subject', 'Student Name', 'Grade'];
    fputcsv($file, $header);


    foreach ($rankings as $className => $subjectRankings) {
        foreach ($subjectRankings as $subject => $students) {
            foreach ($students as $student) {
               
                $row = [
                    $className,        
                    $subject,            
                    $student['name'],    
                    $student['grade']    
                ];

                
                fputcsv($file, $row);
            }
        }
    }

   
    fclose($file);
}
function exportSchoolSubjectRankingsToCsv() {
    
    $subjectRankings = generateSchoolSubjectRankings();

    $dir = 'export';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

   
    $timestamp = date('Y-m-d_His');
    $filename = $dir . '/school_subject_rankings_' . $timestamp . '.csv';


    $file = fopen($filename, 'w');

    $header = ['Subject', 'Name', 'Class', 'Average Grade'];
    fputcsv($file, $header);


    foreach ($subjectRankings as $subject => $students) {
        foreach ($students as $student) {
           
            $row = [
                $subject,            
                $student['name'],   
                $student['class'],  
                round($student['average'], 2) 
            ];
            fputcsv($file, $row);
        }
    }

   
    fclose($file);

   
}
function calculateSubjectAveragesForAllStudents() {
    $allClasses = getKlasse(); 
    $subjectAverages = [];     

   
    foreach ($allClasses as $index => $className) {
       
        $students = loadClassNamesWithSubjects($index);

       
        foreach ($students as $student) {
           
            foreach ($student['subjects'] as $subject => $grades) {
               
                if (count($grades) > 0) {
                   
                    $averageGrade = array_sum($grades) / count($grades);
                    
                    $subjectAverages[] = [
                        'clasname'=>$className,
                        'name' => $student['name'],   
                        'subject' => $subject,     
                        'average' => round($averageGrade, 2) 
                    ];
                }
            }
        }
    }

    return $subjectAverages;
}
function saveSubjectAveragesToCsv() {
  
    $averages = calculateSubjectAveragesForAllStudents();
    
    
    $dir = 'export';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true); 
    }
    $timestamp = date('Y-m-d_His');
    $filename = $dir . '/subject_averages_' . $timestamp . '.csv';

    $file = fopen($filename, 'w');

    $header = ['Class', 'Student Name', 'Subject', 'Average'];
    fputcsv($file, $header);

    foreach ($averages as $average) {
        $row = [
            $average['clasname'], 
            $average['name'],    
            $average['subject'],   
            $average['average']   
        ];

       
        fputcsv($file, $row);
    }

    
    fclose($file);



}
function calculateAndRankClasses() {
    $allClasses = getKlasse(); 
    $subjectAveragesByClass = [];
    $overallAverages = [];

    // Minden osztályhoz kiszámoljuk a tantárgyankénti átlagokat
    foreach ($allClasses as $index => $className) {
        $students = loadClassNamesWithSubjects($index); 
        $subjectSums = [];
        $subjectCounts = [];
        
        foreach ($students as $student) {
            foreach ($student['subjects'] as $subject => $grades) {
                if (!isset($subjectSums[$subject])) {
                    $subjectSums[$subject] = 0;
                    $subjectCounts[$subject] = 0;
                }
                $subjectSums[$subject] += array_sum($grades);
                $subjectCounts[$subject] += count($grades);
            }
        }

        $averages = [];
        $totalSum = 0;
        $totalCount = 0;

        foreach ($subjectSums as $subject => $sum) {
            $average = $subjectCounts[$subject] > 0 ? $sum / $subjectCounts[$subject] : 0;
            $averages[$subject] = round($average, 2);

            $totalSum += $sum;
            $totalCount += $subjectCounts[$subject];
        }

       
        $subjectAveragesByClass[$className] = $averages;

        
        $overallAverages[$className] = $totalCount > 0 ? round($totalSum / $totalCount, 2) : 0;
    }

   
    $subjectRankings = [];
    foreach (getSubjects() as $subject) {
        $subjectRankings[$subject] = [];
        foreach ($subjectAveragesByClass as $className => $averages) {
            if (isset($averages[$subject])) {
                $subjectRankings[$subject][] = [
                    'class' => $className,
                    'average' => $averages[$subject]
                ];
            }
        }
      
        usort($subjectRankings[$subject], function($a, $b) {
            return $b['average'] <=> $a['average']; 
        });
     
        $subjectRankings[$subject] = [
            'best' => $subjectRankings[$subject][0],
            'worst' => end($subjectRankings[$subject])
        ];
    }

    $overallRanking = [];
    foreach ($overallAverages as $className => $average) {
        $overallRanking[] = [
            'class' => $className,
            'average' => $average
        ];
    }
    usort($overallRanking, function($a, $b) {
        return $b['average'] <=> $a['average']; 
    });
    $overallRanking = [
        'best' => $overallRanking[0],
        'worst' => end($overallRanking)
    ];

    return [
        'subjectRankings' => $subjectRankings,
        'overallRanking' => $overallRanking
    ];
}

function exportOverallRankingToCSV($result) {
   
    $directory = 'export';
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true); 
    }
    $timestamp = date('Y-m-d_His');

    $filename = $directory . '/overall_ranking'.$timestamp.'.csv';
    $file = fopen($filename, 'w');
    

    fputcsv($file, ['Osztály', 'Átlag']);
    

    foreach ($result['overallRanking'] as $class) {
        fputcsv($file, [$class['class'], round($class['average'], 2)]);
    }


    fclose($file);
}
function exportSubjectRankingsToCSV($result) {
  
    $directory = 'export';
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $timestamp = date('Y-m-d_His');
    $filename = $directory . '/subject_rankings'.$timestamp.'.csv';
    $file = fopen($filename, 'w');


    fputcsv($file, ['Tantárgy', 'Osztály', 'Átlag']);


    foreach ($result['subjectRankings'] as $subject => $rankings) {

        foreach ($rankings as $classRanking) {
            fputcsv($file, [
                $subject,                  
                $classRanking['class'],   
                round($classRanking['average'], 2)
            ]);
        }
    }
    fclose($file);
}
function calculateSchoolSubjectAverages() {
    $allClasses = getKlasse(); 
    $subjectSums = []; 
    $subjectCounts = [];

    // Minden osztály tanulóinak jegyeit összegyűjtjük
    foreach ($allClasses as $index => $className) {
        $students = loadClassNamesWithSubjects($index); 
        foreach ($students as $student) {
            foreach ($student['subjects'] as $subject => $grades) {
                if (!isset($subjectSums[$subject])) {
                    $subjectSums[$subject] = 0;
                    $subjectCounts[$subject] = 0;
                }
                $subjectSums[$subject] += array_sum($grades);
                $subjectCounts[$subject] += count($grades);
            }
        }
    }


    $subjectAverages = [];
    foreach ($subjectSums as $subject => $sum) {
        $subjectAverages[$subject] = $subjectCounts[$subject] > 0 
            ? round($sum / $subjectCounts[$subject], 2) 
            : 0; 
    }

    return $subjectAverages;
}
function saveOverAllAveragesToCSV($subjectAverages) {
    $exportDir = 'export';
    $timestamp = date('Y-m-d_His');
    $fileName = $exportDir . '/subject_averages'. $timestamp.'.csv'; 

   
    if (!is_dir($exportDir)) {
        mkdir($exportDir, 0777, true); 
    }

   
    $file = fopen($fileName, 'w');
    if (!$file) {
        echo "Hiba: A fájlt nem lehet megnyitni a következő helyen: $fileName";
        return false;
    }

 
    fputcsv($file, ['Subject', 'Average']);

   
    foreach ($subjectAverages as $subject => $average) {
        fputcsv($file, [$subject, $average]);
    }

   
    fclose($file);

    return true;
}


function calculateStudentAveragesByClass() {
    $allClasses = getKlasse(); 
    $classStudentAverages = [];

    // Minden osztályhoz kiszámítjuk a tanulók összátlagát
    foreach ($allClasses as $index => $className) {
        $students = loadClassNamesWithSubjects($index);
        $classStudentAverages[$className] = []; 

        foreach ($students as $student) {
            $totalGrades = 0;
            $gradeCount = 0;

            foreach ($student['subjects'] as $grades) {
                $totalGrades += array_sum($grades); 
                $gradeCount += count($grades);     
            }

           
            $average = $gradeCount > 0 ? round($totalGrades / $gradeCount, 2) : 0;

           
            $classStudentAverages[$className][] = [
                'name' => $student['name'],
                'average' => $average       
            ];
        }
    }

    return $classStudentAverages; 
}


















