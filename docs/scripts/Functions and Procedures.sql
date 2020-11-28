CREATE DEFINER=`virtusbc_tectools`@`%` PROCEDURE `getUserByEmail`(
	IN `p_Email` VARCHAR(50)
)
LANGUAGE SQL
NOT DETERMINISTIC
READS SQL DATA
SQL SECURITY DEFINER
COMMENT ''
BEGIN
	SELECT * FROM Users WHERE Email = p_Email;
END;

CREATE PROCEDURE `getUserByEmailAndPassword`(
	IN `p_Email` VARCHAR(255),
	IN `p_Password` VARCHAR(255)
)
LANGUAGE SQL
NOT DETERMINISTIC
READS SQL DATA
SQL SECURITY DEFINER
COMMENT ''
BEGIN
	SELECT * FROM Users WHERE Email = p_Email and Password = p_Password;
END

CREATE PROCEDURE `addUser`(
	IN `p_FirstName` VARCHAR(255),
	IN `p_LastName` VARCHAR(50),
	IN `p_Email` VARCHAR(50),
	IN `p_Password` VARCHAR(50),
	IN `p_Phone` VARCHAR(50),
	IN `p_Address` VARCHAR(50),
	IN `p_ZipCode` VARCHAR(50),
	IN `p_City` VARCHAR(50)
)
LANGUAGE SQL
NOT DETERMINISTIC
MODIFIES SQL DATA
SQL SECURITY DEFINER
COMMENT ''
BEGIN
	INSERT INTO Users (FirstName, LastName, Email, `Password`, Phone, Address, ZipCode, City)
	VALUES(p_FirstName, p_LastName, p_Email, p_Password, p_Phone, p_Address, p_ZipCode, p_City);
END