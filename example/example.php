<head>
    <title>Example</title>
</head>

<h1 style='text-align: center; font-family: sans-serif; margin-top: 50px'>phpSqlToClass example</h1>

<?php

/*****************************************************************
 *  Initialization, don't forget to set your parameters          *
 *  in getDatabase() function and import phpToClass.sql          *
 *****************************************************************/

require_once "My_class.php";
My_class::$DB = getDatabase();

/*****************************************************************
 *     First test, recover object from DB and update it          *
 *****************************************************************/

$id = "test001";
$myObject = My_class::wrapper($id);
if (!is_null($myObject)) {
    echo "The object with id $id exist!<br><br>";
    echo "The object is:<br>" . $myObject . "<br>";
    echo "Now let's update it...<br>";
    $myObject->update("New name", $myObject->getDate(), $myObject->getTimestamp(), !$myObject->isValid());
    echo "The new object name is : " . $myObject->getName() . "<br>";
    echo "The new object valid status is : " . $myObject->isValid() . "<br>";
    echo "Reverting...<br>";
    $myObject->setName("Test");
    $myObject->setValid(!$myObject->isValid());
}
else {
    echo "The object with id $id doesn't exist in database!<br>";
}

echo "<br>";

/*****************************************************************
 *         Second test, create object and delete it              *
 *****************************************************************/

$id = "test002";
$myObject = My_class::wrapper($id);
if (!is_null($myObject)) {
    echo "The object with id $id exist!<br><br>";
    echo "The object is:<br>" . $myObject;
}
else {
    echo "The object with id $id doesn't exist! Let's create it...<br>";

    if (My_class::create($id, "Test 2", date("Y-m-d"), date("Y-m-d H:i:s"), 0)) {
        $myObject = My_class::wrapper($id);
        echo "The new object is:<br>" . $myObject . "<br>";

        echo "Now let's delete it...<br>";
        if ($myObject->delete()) {
            echo "Object successfully deleted!<br>";
        }
        else {
            echo "Error while deleting the object.<br>";
        }
    }
    else {
        echo "Error while creating new object.";
    }
}