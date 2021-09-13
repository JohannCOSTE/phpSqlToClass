# phpSqlToClass

## Description
These functions are intended to create automatically php classes from MySQL tables.

## Usage
### Generate the file
1. Fill SQL database information in `getDatabase` function of `utils.php` file.
2. Access to `phpSqlToClass.php`
3. Enter desired database and table
4. Choose if you prefer the output on browser or on file
5. Click on `Generate`
6. Auto indent file with you IDE

### Use the class
*Extracted from example directory.* 
#### Add new object to database
```
if(My_class::create("myId", "Test 1", date("Y-m-d"), time(), 0)){
    echo "The object is created in database!";
}
else{
    echo "Error while creating new object";
}
```

#### Map an SQL entry to you object
```
$id = "myId";
$myObject = My_class::wrapper($id);
if(!is_null($myObject)){
    echo "The object is:<br>" . $myObject;
}
else{
    echo "The object with id $id doesn't exist in database!<br>";
}
```

#### Delete SQL entry from database
```
$id = "myId";
$myObject = My_class::wrapper($id);
if(!is_null($myObject) && $myObject->delete()){
    echo "Object successfully deleted!<br>";
}
else{
    echo "Error while deleting the object.<br>";
}
```

#### Update SQL entry from database
```
$id = "myId";
$myObject = My_class::wrapper($id);
if(!is_null($myObject) && $myObject->update("New name", "2021-09-12", "2021-09-12 08:04:30", 0)){
    echo "Object successfully updated!<br>";
}
else{
    echo "Error while updating the object. Maybe setted values are the same as before.<br>";
}
```

#### Getters
```
$id = "myId";
$myObject = My_class::wrapper($id);
if(!is_null($myObject)){
    echo "The name of the object is {$myObject->getName()}.<br>";
}
else {
    echo "The object with id $id doesn't exist in database!<br>";
}
```

#### Setters
```
$id = "myId";
$myObject = My_class::wrapper($id);
if(!is_null($myObject) && $myObject->setName("New name")){
    echo "The new name of the object is {$myObject->getName()}.<br>";
}
else {
    echo "The new name hasn't been changed.<br>";
}
```
