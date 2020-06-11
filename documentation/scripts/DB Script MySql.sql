/*CREATE TABLE UserPermissions (
PermissionID int NOT NULL AUTO_INCREMENT,
PermissionName nvachar(255),
PRIMARY KEY (PermissionID)
);*/

DROP TABLE CheckIns;
DROP TABLE CategoryTools;
DROP TABLE Tools;
DROP TABLE Categories;
DROP TABLE Manufacturers;
DROP TABLE Users;

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(100) DEFAULT NULL,
  `content` longtext,
  `include` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `is_admin_page` tinyint(1) NOT NULL,
  `require_login` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE Users (
UserID int NOT NULL AUTO_INCREMENT,
FirstName nvarchar(255),
LastName nvarchar(255),
Email nvarchar(255),
Password nvarchar(255),
Phone nvarchar(25),
Address nvarchar(255),
ZipCode nvarchar(10),
City nvarchar(255),
StripeID nvarchar(255) NOT NULL default '',
Level int(11) NOT NULL default '1',
PRIMARY KEY (UserID)
);

CREATE TABLE Manufacturers (
ManufacturerID int NOT NULL AUTO_INCREMENT,
ManufacturerName nvarchar(255),
PRIMARY KEY (ManufacturerID)
);

CREATE TABLE Categories(
CategoryID int NOT NULL AUTO_INCREMENT,
CategoryName nvarchar(255),
PRIMARY KEY (CategoryID)
);

CREATE TABLE Tools (
ToolID int NOT NULL AUTO_INCREMENT,
FK_ManufacturerID int NOT NULL,
BarCode nvarchar(13),
ToolName nvarchar(255),
Description nvarchar(1000),
Status int,
Image nvarchar(1000),
PRIMARY KEY (ToolID),
FOREIGN KEY (FK_ManufacturerID) REFERENCES Manufacturers(ManufacturerID)
);

CREATE TABLE CategoryTools (
FK_CategoryID int NOT NULL,
FK_ToolID int NOT NULL,
FOREIGN KEY (FK_CategoryID) REFERENCES Categories(CategoryID),
FOREIGN KEY (FK_ToolID) REFERENCES Tools(ToolID)
);

CREATE TABLE CheckIns (
CheckInID int NOT NULL AUTO_INCREMENT,
FK_UserID int NOT NULL,
FK_ToolID int NOT NULL,
StartDate datetime default NOW(),
EndDate datetime,
CheckedOut bit,
Comment nvarchar(1000),
PRIMARY KEY (CheckInID),
FOREIGN KEY (FK_UserID) REFERENCES Users(UserID),
FOREIGN KEY (FK_ToolID) REFERENCES Tools(ToolID)
);

CREATE TABLE `Reservations` (
	ReservationID INT NOT NULL AUTO_INCREMENT,
	`FK_UserID` INT(11) NOT NULL,
	`FK_ToolID` INT(11) NOT NULL,
	`StartDate` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`EndDate` DATETIME NULL,
	FOREIGN KEY (FK_UserID) REFERENCES Users(UserID),
	FOREIGN KEY (FK_ToolID) REFERENCES Tools(ToolID),
	PRIMARY KEY (ReservationID)
);


CREATE TABLE StripePlans (
StripeID nvarchar(255),
SubscriptionName NVARCHAR(255),
MaxCheckouts int NOT NULL DEFAULT 0,
CheckoutDuration int NOT NULL DEFAULT 0,
PRIMARY KEY(StripeID)
);

/*
CREATE TABLE Comments(
FK_UserID int NOT NULL,
FK_ToolID int NOT NULL,
FOREIGN KEY (FK_UserID) REFERENCES Users(UserID),
FOREIGN KEY (FK_ToolID) REFERENCES Tools(ToolID),
Comment nvarchar(1000),
Created datetime default GETDATE()
);*/