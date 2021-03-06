<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include '../../functions.php';
include '../../config.php';

//New PDO DB connection
$pdo = new Gibbon\sqlConnection();
$connection2 = $pdo->getConnection();

@session_start();

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]['timezone']);

$search = $_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/medicalForm_manage_add.php&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $gibbonPersonID = $_POST['gibbonPersonID'];
    $bloodType = $_POST['bloodType'];
    $longTermMedication = $_POST['longTermMedication'];
    $longTermMedicationDetails = $_POST['longTermMedicationDetails'];
    $tetanusWithin10Years = $_POST['tetanusWithin10Years'];

    //Validate Inputs
    if ($gibbonPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('gibbonPersonID' => $gibbonPersonID);
            $sql = 'SELECT * FROM gibbonPersonMedical WHERE gibbonPersonID=:gibbonPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('gibbonPersonID' => $gibbonPersonID, 'bloodType' => $bloodType, 'longTermMedication' => $longTermMedication, 'longTermMedicationDetails' => $longTermMedicationDetails, 'tetanusWithin10Years' => $tetanusWithin10Years);
                $sql = 'INSERT INTO gibbonPersonMedical SET gibbonPersonID=:gibbonPersonID, bloodType=:bloodType, longTermMedication=:longTermMedication, longTermMedicationDetails=:longTermMedicationDetails, tetanusWithin10Years=:tetanusWithin10Years';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
