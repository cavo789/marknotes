' ===========================================================================
' Marknotes conversion utilitiy
' https://github.com/cavo789/marknotes
'
' Author : Christophe Avonture
' Date   : August 2017
'
' This script is for Windows OS. Make a copy of this script in a folder where you've
' .docx or .odt files and the script, thanks to pandoc (should be first installed), will
' convert these files into .md (markdown) ones.
'
' The .md files will then be used in the marknotes interface.
'
' The conversion will :
'   * convert any .docx and .odt files present in the folder and his subfolders
'   * create one .md file by .docx or .odt file
'   * will extract every images present in .docx or .odt and will save them in a .images folder
'     (there will be one subfolder in .images by processed file)
'   * after the conversion, the original file will be moved in a .files folder
'
' Once the script has finished, you'll have :
'    yourfolder
'        \.files      <-- the folder where the original file (.docx or .odt) have been moved
'        \.images     <-- the folder where embeded images (in documents) have been saved
'        \*.md        <-- one .md file by converted file
'
'   The name of the folders (.files and .images) starts with a dot so these folders are "hidden"
'   and therefore not displayed in the tree of marknotes (under Linux, a name starting with a
'   dot means "hidden")
'
' ===========================================================================

' ### START OF CONFIGURATION #############################################
'
' Mention here the full path to the pandoc.exe on your system.
' If not yet installed, please install pandoc from http://pandoc.org/installing.html
Const cPandocExecutable = "C:\Christophe\Tools\pandoc\pandoc.exe"
'
' Enable or not the debug mode (is more verbose)
Const cDebug  = False
'
' ### END OF CONFIGURATION ###############################################

' ---------------------------------------------------------------------------
' ---------------------------------------------------------------------------
' ---------------------------------------------------------------------------
' ---------------------------------------------------------------------------
' ---------------------------------------------------------------------------
' Except if you know what you're doing, don't change anything below this line
' ---------------------------------------------------------------------------

Dim cConvert, cFunctions, cFileSystem, cPandoc

' ---------------------------------------------------------------------------
'
' Define a class for the generic functions
'
' ---------------------------------------------------------------------------

Class clsFunctions

	Private bDebugMode

	Public Property Let DebugMode(bOnOff)
		bDebugMode = bOnOff
	End Property

	Public Property Get DebugMode()
		DebugMode = bDebugMode
	End Property

	' -------------------------------
	'
	' Little helper for echoing a message.
	' Under Windows (double-clic on the script), a echo is a popup so by
	' displaying a lot of information, this will be very annoying.
	'
	' echos will be always done
	'   * if bAlways is set on True
	'   * if the script was started with cscript i.e. console mode
	'   * if bOnlyDebug means that the echo will be done only if the debug mode is set
	'
	' -------------------------------

	Sub Echo(sLine, bAlways, bOnlyDebug, wIndent)

		If InStr(1, WScript.FullName, "wscript", vbTextCompare) Then
			wIndent=0
			sLine = Replace(sLine, "|", vbCrLf)
		Else
			sLine = Replace(sLine, "|", "")
		End if

		If (bAlways) Then
			wScript.Echo Space(wIndent * 3) & sLine
		Else
			If InStr(1, WScript.FullName, "cscript", vbTextCompare) Then
				' Started with cscript => do the echo on the console
				If Not (bOnlyDebug) or ( (bOnlyDebug and DebugMode()) ) then
					wScript.Echo Space(wIndent * 3) & sLine
				End If
			End If
		End If

	End Sub

	' -------------------------------
	'
	' Sanitize a filename, remove special characters and keep only ASCII chars.
	'
	' -------------------------------

	Function SanitizeFileName(sFileName)

	Dim objRegExp
	Dim sResult

		Set objRegExp = New Regexp

		objRegExp.IgnoreCase = True
		objRegExp.Global = True

		' Strip non-ASCII characters
		objRegExp.Pattern = "[^\u0000-\u007F]"
		sResult = objRegExp.Replace(sFileName, "-")

		' Replace some invalid characters
		objRegExp.Pattern = "[(?*"",\\<>«&#~%{}\(\)\'+_û–.@:\/!;]+"
		sResult = objRegExp.Replace(sResult, "-")

		objRegExp.Pattern = "\-+"
		sResult = objRegExp.Replace(sResult, "-")

		' chr(150) is like a minus sign but bigger, used sometimes in Winword.
		' That character will give problems so convert it as a minus one
		sResult=replace(sResult, chr(150), "-")

		SanitizeFileName = sResult

	End Function

	' -------------------------------
	'
	' in_array helper, check if a value is present in an array
	'
	' -------------------------------

	Function in_array(value, arr)

	Dim hay

		in_array = False

		value = trim(value)

		For Each hay in arr

			If trim(hay) = value Then
				in_array = True
				Exit For
			End If

		Next

	End Function

	' -------------------------------
	'
	' Remove specific tags from a string
    ' http://webdevel.blogspot.be/2005/05/strip-tags-vbscript.html
	'
	' For instance = to remove span, do something like :
	'
	'    StripTags("<html>...<span id='JJ'>blabla</span>...", "span")
	' -------------------------------

	Private Function StripTags(sValue, sTag)

		' set 'sTag' to empty string to strip all sTag
		If sTag = "" Then sTag = "[a-zA-Z]+"

		Set objRegExp = New RegExp

		objRegExp.IgnoreCase = True
		objRegExp.Global = True

		' tag to remove (based on http://regexplib.com/REDetails.aspx?regexp_id=211)
		objRegExp.Pattern = "</?("+sTag+")(\s+\w+=(\w+|""[^""]*""|'[^']*'))*\s*?/?>"

		StripTags = objRegExp.Replace(sValue, "")

	End Function

	' -------------------------------
	'
	' Conversion from .docx can generate a lot of <span> for, f.i., table
	' of contents. Remove these span
	'
	' -------------------------------

	Public Function RemoveHTMLTags(sValue)

		sValue = StripTags(sValue, "span")
		RemoveHTMLTags = sValue

	End Function

	' -------------------------------
	'
	' Remove non-ASCII characters from a string, replace them by an empty string
	' https://stackoverflow.com/a/37025007/1065340
	'
	' -------------------------------

	Public Function RemoveNonASCII(sValue)

		Set objRegExp = New Regexp

		objRegExp.Global = True
		objRegExp.Pattern = "[^\u0000-\u007F]"

		sValue = objRegExp.Replace(sValue, "")

		Set objRegExp = Nothing

		RemoveNonASCII = sValue

	End Function

End Class

' ---------------------------------------------------------------------------
'
' Define a class for file's operations
'
' ---------------------------------------------------------------------------

Class clsFileSystem

	Private bDebugMode
	Private objFSO
	Private colFiles

	Public Property Let DebugMode(bOnOff)
		bDebugMode = bOnOff
	End Property

	Public Property Get DebugMode()
		DebugMode = bDebugMode
	End Property

	' -------------------------------
	'
	' Change the working, current, directory
	'
	' -------------------------------

	Public Sub Chdir(sFolderName)

		Set objShell = CreateObject("WScript.Shell")
		objShell.CurrentDirectory = sFolderName
		Set objShell = Nothing

	End Sub

	' -------------------------------
	'
	' Recursive function, called by RetrieveListOfFiles
	' Scan every subfolders and retrieve the list of files that should be processed
	'
	' -------------------------------

	Private Sub ShowSubFolders(Folder, arrSupportedFileExt)

		Dim sParentName

		For Each Subfolder in Folder.SubFolders

			Set objFolder = objFSO.GetFolder(Subfolder.Path)
			Set objFiles = objFolder.Files

			For Each f in objFiles

				' Only process supported files
				If (cFunctions.in_array(objFSO.GetExtensionName(f.Name), arrSupportedFileExt)) Then

					' And only if there are not located in a folder called ".files"

					sParentName = objFSO.GetParentFolderName(f.ParentFolder & "\" & f.Name)
					sParentName = Mid(sParentName, InStrRev(sParentName, "\") + 1)

					If (sParentName <> ".files") Then
						colFiles.Add f.ParentFolder & "\" & f.Name
					End If
				End if
			Next

			Call ShowSubFolders(Subfolder, arrSupportedFileExt)

			Set objFolder = Nothing
			Set objFiles = Nothing

		Next

	End Sub

	' -------------------------------
	'
	' Retrieve the list of files that should be processed
	'
	' -------------------------------

	Public Function RetrieveListOfFiles(sFolder, arrSupportedFileExt)

		' Initialize a collection that will contains the list of files that needs to be process
		Set colFiles = CreateObject("System.Collections.ArrayList")

		colFiles.Clear

		For Each f In objFSO.GetFolder(sFolder).Files
			If (cFunctions.in_array(objFSO.GetExtensionName(f.Name), arrSupportedFileExt)) Then
				colFiles.Add f.ParentFolder & "\" & f.Name
			End if
		Next

		' And make recursion
		Call ShowSubfolders(objFSO.GetFolder(sFolder), arrSupportedFileExt)

		' Sort the list
		colFiles.Sort()

		Set RetrieveListOfFiles = colFiles

	End Function

	' -------------------------------
	'
	' Archive the file
	'
	' -------------------------------

	Sub MoveFile(sSourceName, sTargetName)

		Call cFunctions.Echo ("Archive " & sSourceName, False, True, 2)

		' If the .md file is still there, move the original file into the .files subfolder
		If Not DebugMode() Then

			If objFSO.FileExists(sTargetName) Then
				objFSO.DeleteFile(sTargetName)
			End if

			Call objFSO.MoveFile(sSourceName, sTargetName)

		Else

			Call cFunctions.Echo("Don't archive when DebugMode is set", False, True, 3)

		End if

	End Sub

	' -------------------------------
	'
	' Rename a file
	'
	' -------------------------------

	Sub RenameFile(OldName, NewName)

		Call objFSO.MoveFile(OldName, NewName)

	End Sub

	' -------------------------------
	'
	' Read the file's content. Be sure to open as a UTF-8 file
	'
	' -------------------------------

	Function GetFileContent(sFileName)

		Set objBinaryStream = CreateObject("ADODB.Stream")

		objBinaryStream.CharSet = "utf-8"
		objBinaryStream.Open

		objBinaryStream.LoadFromFile sFileName

		sContent = objBinaryStream.ReadText()

		objBinaryStream.Close

		Set objBinaryStream = Nothing

		GetFileContent = sContent

	End Function

	' -------------------------------
	'
	' Create a file, UTF-8 encoded
	'
	' -------------------------------

	Sub MakeUTF8(sFileName, sContent)

		Dim objBinaryStream

		' If the file already exists, delete it since we'll recreate it
		If objFSO.FileExists(sFileName) Then

			objFSO.DeleteFile(sFileName)

		End If

		Set objBinaryStream = CreateObject("ADODB.Stream")
		objBinaryStream.CharSet = "utf-8"
		objBinaryStream.Open
		objBinaryStream.WriteText Replace(sContent, vbCrLF, vbLF), 0
		objBinaryStream.SaveToFile sFileName, 2
		objBinaryStream.Close
		Set objBinaryStream = Nothing

	End Sub

	Private Sub Class_Initialize()
		Set objFSO = CreateObject("Scripting.FileSystemObject")
    End Sub

	Private Sub Class_Terminate()
		Set objFSO = Nothing
    End Sub

End Class

Class clsPandoc

Private bDebugMode
Private sExecutable, sBaseName, sMediaFolder, sSourceExtension, sTargetFile
Private objFSO, objShell
Private sTmp

	Public Property Let DebugMode(bOnOff)
		bDebugMode = bOnOff
	End Property

	Public Property Get DebugMode()
		DebugMode = bDebugMode
	End Property

	Public Property Let BaseName(sValue)
		sBaseName = sValue
	End Property

	Public Property Get BaseName()
		BaseName = sBaseName
	End Property

	Public Property Let MediaFolder(sValue)
		sMediaFolder = sValue
	End Property

	Public Property Get MediaFolder()
		MediaFolder = sMediaFolder
	End Property

	Public Property Let SourceExtension(sValue)
		sSourceExtension = sValue
	End Property

	Public Property Get SourceExtension()
		SourceExtension = sSourceExtension
	End Property

	Public Property Let TargetFile(sValue)
		sTargetFile = sValue
	End Property

	Public Property Get TargetFile()
		TargetFile = sTargetFile
	End Property

	' -------------------------------
	'
	' Set the path to the pandoc.exe file
	'
	' -------------------------------

	Public Property Let Executable(sFileName)

		If Not objFSO.FileExists(sFileName) Then

			Call cFunctions.Echo("ERROR - Pandoc isn't installed on your system, " & _
			   "please install it from http://pandoc.org/installing.html.||" & _
			   "Once done, edit this .vbs script and adjust the cPandocExecutable " & _
			   "constant to where you've save pandoc", True, False, 1)

			wScript.Quit

		End If

		sExecutable = sFileName

	End Property

	Public Property Get Executable()
		Executable = sExecutable
	End Property

	' -------------------------------
	'
	' Build the pandoc command line with all required options
	'
	' -------------------------------

	Private Function GetCommandLine()

		' Build the pandoc command line
		sTmp = Executable()

		' -w (write-to) indicate the target desired format.
		' Here, convert to a markdown_github format
		sTmp = sTmp & " -w markdown_github"

		' Convert accentuated characters in their HTML equivalent
		' (f.i. & will be saved as &amp;)
		sTmp = sTmp & " --ascii"

		' Set the heading style to #, ##, ### and not lines
		'(see http://pandoc.org/MANUAL.html#atx-style-headers)
		sTmp = sTmp & " --atx-headers"

		' With none, pandoc will not wrap lines in the generated document.
		sTmp = sTmp & " --wrap=none"

		' The file is a stand-alone one (stand-alone)
		sTmp = sTmp & " -s"

		' Extract images and saved them in a .images/FILENAME subfolder
		sTmp = sTmp & " --extract-media=.images/" & MediaFolder()

		' Output filename
		sTmp = sTmp & " -o """ & TargetFile() & """"

		' And the source filename
		sTmp = sTmp & " """ & BaseName()  & "." & SourceExtension() & """"

		'Call cFunctions.Echo (sTmp, False, True, 2)

		GetCommandLine = sTmp

	End Function

	' -------------------------------
	'
	' Run pandoc, make the conversion
	'
	' -------------------------------

	Public Sub Run

		sPandoc = GetCommandLine()

		' Don't show the window and, important, wait until the pandoc script is finished
		Set objShell = CreateObject("WScript.Shell")
		objShell.Run sPandoc, 0, 1
		Set objShell = nothing

	End Sub

	Private Sub Class_Initialize()
		Set objFSO = CreateObject("Scripting.FileSystemObject")
    End Sub

	Private Sub Class_Terminate()
		Set objFSO = Nothing
    End Sub

End Class

' ---------------------------------------------------------------------------
'
' Define the main class for the conversion
'
' ---------------------------------------------------------------------------

Class clsConvert

Private bDebugMode
Private sSourceFileName, sInitialDirectory
Private objFSO

	Public Property Let DebugMode(bOnOff)
		bDebugMode = bOnOff
	End Property

	Public Property Get DebugMode()
		DebugMode = bDebugMode
	End Property

	Public Property Let InitialDirectory(sValue)
		sInitialDirectory = sValue
	End Property

	Public Property Get InitialDirectory()
		InitialDirectory = sInitialDirectory
	End Property

	Public Property Let SourceFileName(sValue)
		sSourceFileName = sValue
	End Property

	Public Property Get SourceFileName()
		SourceFileName = sSourceFileName
	End Property

	Public Property Get SourceFileRelativeName()
		SourceFileRelativeName = Replace(sSourceFileName, sInitialDirectory, "")
	End Property

	Public Property Get SourceFileBaseName()
		SourceFileBaseName = objFSO.GetBaseName(sSourceFileName)
	End Property

	Public Property Get SourceFileParentFolder()
		SourceFileParentFolder = objFSO.GetParentFolderName(objFSO.GetFile(sSourceFileName)) & "\"
	End Property

	Public Property Get SourceFileExtensionName()
		SourceFileExtensionName = objFSO.GetExtensionName(objFSO.GetFile(sSourceFileName))
	End Property

	Public Property Get TargetFileName()
		TargetFileName = Replace(sSourceFileName, SourceFileExtensionName() , "md")
	End Property

	Public Property Get TargetMediaFolder()
		TargetMediaFolder = Replace(SourceFileBaseName(), " ", "-")
	End Property

	' -------------------------------
	'
	' Display a small introduction
	'
	' -------------------------------

	Sub ShowHeader

		If InStr(1, WScript.FullName, "cscript", vbTextCompare) Then
			Call cFunctions.Echo("==============================================================", False, False, 1)
			Call cFunctions.Echo("= Marknotes Convert utility                                  =", False, False, 1)
			Call cFunctions.Echo("= Convert .docx and .odt files into .md ones                 =", False, False, 1)
			Call cFunctions.Echo("=                                                            =", False, False, 1)
			Call cFunctions.Echo("= Remark : only process the current folder, not recursively  =", False, False, 1)
			Call cFunctions.Echo("==============================================================", False, False, 1)
			Call cFunctions.Echo("", False, False, 0)
		Else
			' Started in Windows by wecript.exe : the user has made a double-clic on the file.
			wScript.Echo "Marknotes Convert utility is running... Please wait"
		End If

	End Sub

	' -------------------------------
	'
	' Sanitize a filename, if needed, rename the file on the disk
	'
	' -------------------------------

	Private Sub SanitizeFileName()

		Dim sNewName

		sNewName = cFunctions.SanitizeFileName(SourceFileBaseName)

		If (sNewName <> SourceFileBaseName) Then

			sNewName = SourceFileParentFolder & sNewName & "." & SourceFileExtensionName()
			Call cFileSystem.RenameFile(SourceFileName, sNewName)

			SourceFilename = sNewname

		End If

	End Sub

	' -------------------------------
	'
	' Process a file
	'
	' -------------------------------

	Sub Run()

		Dim objFile
		Dim sContent, sTemp

		Set objFile = objFSO.GetFile(sSourceFileName)

		' Be sure to have clean filenames
		Call SanitizeFileName()

		' Create subfolders for images and files
		If Not (objFSO.FolderExists(SourceFileParentFolder() & ".images")) Then
			objFSO.CreateFolder(SourceFileParentFolder() & ".images")
		End If

		If Not (objFSO.FolderExists(SourceFileParentFolder() & ".files")) Then
			objFSO.CreateFolder(SourceFileParentFolder() & ".files")
		End If

		' Call pandoc and convert the file
		cPandoc.BaseName = SourceFileBaseName()
		cPandoc.SourceExtension = SourceFileExtensionName()
		cPandoc.TargetFile = TargetFileName()
		cPandoc.MediaFolder = TargetMediaFolder()
		cPandoc.Run

		' Now, read the generated .md file and replace a few things
		If objFSO.FileExists(TargetFileName()) Then

			' Read the file's content
			sContent = cFileSystem.GetFileContent(TargetFileName())

			' Add an intro : a link to the original file
			sContent = "[Link to the original file, before conversion](%URL%.files/" & _
				Replace(SourceFileBaseName()," ","%20") & "." & SourceFileExtensionName() & ")" & vbCrLf & vbCrLf & sContent

			' ---------------------------------------------------------
			'
			' Add the %URL% variable so marknotes will keep absolute filename for images

			sContent = Replace(sContent, "src=" & chr(34) & ".images/", "src=" & chr(34) & "%URL%.images/")

			sContent = cFunctions.RemoveHTMLTags(sContent)

			'sContent = cFunctions.RemoveNonASCII(sContent)

			Call cFileSystem.MakeUTF8(TargetFileName(), sContent)

			If objFSO.FileExists(TargetFileName()) Then
				sTemp = SourceFileParentFolder() & ".files\" & SourceFileBaseName() & "." & SourceFileExtensionName()
				Call cFileSystem.MoveFile(SourceFileName(), sTemp)
			End if

			If DebugMode() Then
				'sContent = cFileSystem.GetFileContent(TargetFileName())
				'Call cFunctions.Echo(sContent, False, True, 0)
			End if

		End if

	End Sub

	Private Sub Class_Initialize()
		Set objFSO = CreateObject("Scripting.FileSystemObject")
    End Sub

	Private Sub Class_Terminate()
		Set objFSO = Nothing
    End Sub

End Class

' ###############
' # Entry point #
' ###############

Dim I

	Set cFunctions = New clsFunctions
	Set cFileSystem = New clsFileSystem
	Set cPandoc = New clsPandoc
	Set cConvert = New clsConvert

	cFunctions.DebugMode = cDebug
	cFileSystem.DebugMode = cDebug
	cConvert.DebugMode = cDebug
	cPandoc.DebugMode = cDebug

	cPandoc.Executable = cPandocExecutable

	Call cConvert.ShowHeader()

	Set objFSO = CreateObject("Scripting.FileSystemObject")

	sInitialDirectory = objFSO.GetAbsolutePathName(".") & "\"

	cConvert.InitialDirectory = sInitialDirectory

	' Retrieve the list of files that should be processed

	Set colFiles = CreateObject("System.Collections.ArrayList")

	Set colFiles = cFileSystem.RetrieveListOfFiles(sInitialDirectory, array("docx","odt"))

	' Process them
	Call cFunctions.Echo ("Number of files that will be processed : #" & colFiles.Count & _
	   vbCrLf, False, False, 1)

	I = 1

	For Each sFile In colFiles

		Set objFile = objFSO.GetFile(sFile)

		cConvert.SourceFileName = objFile.ParentFolder & "\" & objFile.Name

		If (I > 1) Then Call cFunctions.Echo("", False, False, 0)

		Call cFunctions.Echo("Process file " & I & "/" & colFiles.Count & " : " & _
			cConvert.SourceFileRelativeName, False, False, 1)

		If  (objFile.ParentFolder <> sProcessingDir) Then
		    cFileSystem.Chdir(objFile.ParentFolder)
			sProcessingDir = objFile.ParentFolder
		End if

		Call cConvert.Run

		I = I + 1

	Next

	Call cFunctions.Echo("", False, False, 0)

	' Restore the initial directory
	cFileSystem.Chdir(sInitialDirectory)

	Set objFSO = Nothing
	Set colFiles = Nothing

	Set cFunctions = Nothing
	Set cFileSystem = Nothing
	Set cPandoc = Nothing
	Set cConvert = Nothing

	If InStr(1, WScript.FullName, "wscript", vbTextCompare) Then
		wScript.Echo "Marknotes Convert utility has finished."
	End If
