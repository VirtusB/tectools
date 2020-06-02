CREATE TABLE UserPermissions (
PermissionID int IDENTITY(1,1) PRIMARY KEY,
PermissionName nvachar(255)
)
GO

CREATE TABLE Users (
UserID int IDENTITY(1,1) PRIMARY KEY,
FirstName nvarchar(255),
Lastname nvarchar(255),
Email nvarchar(255),
Password nvarchar(255),
Address nvarchar(255),
ZipCode nvarchar(10),
City nvarchar(255),
FK_PermissionLevel int FOREIGN KEY REFERENCES UserPermissions(PermissionID),
)

GO

CREATE TABLE Manufacturers (
ManufacturerID int IDENTITY(1,1) PRIMARY KEY,
ManufacturerName nvachar(255)
)

GO

CREATE TABLE Categories(
CategoryID int IDENTITY(1,1) PRIMARY KEY,
CategoryName nvarchar(255),
)
GO

CREATE TABLE Tools (
ToolID int IDENTITY(1,1) PRIMARY KEY,
BarCode nvachar(13),
ToolName nvarchar(500),
FK_ManufacturerID int FOREIGN KEY REFERENCES Manufacturers(ManufacturerID),
Description nvarchar(max),
Status int,
Image nvarchar(max)
)
GO

CREATE TABLE CategoryTools (
FK_CategoryID int FOREIGN KEY REFERENCES Categories(CategoryID),
FK_ToolID int FOREIGN KEY REFERENCES Tools(ToolID)
)

GO

CREATE TABLE CheckIns (
FK_UserID FOREIGN KEY REFERENCES Users(UserID),
FK_ToolID FOREIGN KEY REFERENCES Tools(ToolID),
StartDate datetime,
EndDate datetime,
CheckedOut bit
)
GO

CREATE TABLE Comments(
FK_UserID FOREIGN KEY REFERENCES Users(UserID),
FK_ToolID FOREIGN KEY REFERENCES Tools(ToolID),
Comment nvarchar(1000),
Created datetime default GETDATE()
)