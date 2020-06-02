/*CREATE TABLE UserPermissions (
PermissionID int NOT NULL AUTO_INCREMENT,
PermissionName nvachar(255),
PRIMARY KEY (PermissionID)
);*/


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
FK_PermissionID int NOT NULL,
FirstName nvarchar(255),
Lastname nvarchar(255),
Email nvarchar(255),
Password nvarchar(255),
Address nvarchar(255),
ZipCode nvarchar(10),
City nvarchar(255),
Level int(11) NOT NULL default '0',
PRIMARY KEY (UserID),
FOREIGN KEY (FK_PermissionID) REFERENCES UserPermissions(PermissionID)
);

CREATE TABLE Manufacturers (
ManufacturerID int NOT NULL AUTO_INCREMENT,
ManufacturerName nvachar(255)
);

CREATE TABLE Categories(
CategoryID int NOT NULL AUTO_INCREMENT,
CategoryName nvarchar(255),
);

CREATE TABLE Tools (
ToolID int NOT NULL AUTO_INCREMENT,
FK_ManufacturerID int NOT NULL,
BarCode nvachar(13),
ToolName nvarchar(500),
Description nvarchar(max),
Status int,
Image nvarchar(max),
FOREIGN KEY (FK_ManufacturerID) REFERENCES Manufacturers(ManufacturerID)
);

CREATE TABLE CategoryTools (
FK_CategoryID int NOT NULL,
FK_ToolID int NOT NULL,
FOREIGN KEY (FK_CategoryID) REFERENCES Categories(CategoryID),
FOREIGN KEY (FK_ToolID) REFERENCES Tools(ToolID)
);

CREATE TABLE CheckIns (
FK_UserID int NOT NULL,
FK_ToolID int NOT NULL,
FOREIGN KEY (FK_UserID) REFERENCES Users(UserID),
FOREIGN KEY (FK_ToolID) REFERENCES Tools(ToolID),
StartDate datetime default GETDATE(),
EndDate datetime default (NOW() + INTERVAL 7 day),
CheckedOut bit,
Comment nvarchar(1000)
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