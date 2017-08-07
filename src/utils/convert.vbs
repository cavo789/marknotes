Dim objFSO, objFile
Dim objShell
Dim arrSupportedFileExt

Const cPandoc="C:\Christophe\Tools\pandoc\pandoc.exe"

Sub ShowHeader
	wScript.echo Space(3) + "=============================================================="
	wScript.echo Space(3) + "= Marknotes Convert utility                                  ="
	wScript.echo Space(3) + "= Convert .docx and .odt files into .md ones                 ="
	wScript.echo Space(3) + "=                                                            ="
	wScript.echo Space(3) + "= Remark : only process the current folder, not recursively  ="
	wScript.echo Space(3) + "=============================================================="
End Sub

Function SanitizeFileName (sFileName)

Dim objRegExp, outputStr

	Set objRegExp = New Regexp

	objRegExp.IgnoreCase = True
	objRegExp.Global = True

	' Strip non-ASCII characters
	objRegExp.Pattern = "[^\u0000-\u007F]"
	outputStr = objRegExp.Replace(sFileName, "-")

	' Replace some invalid characters
	objRegExp.Pattern = "[(?*"",\\<>«&#~%{}\'+_û–.@:\/!;]+"
	outputStr = objRegExp.Replace(outputStr, "-")

	objRegExp.Pattern = "\-+"
	outputStr = objRegExp.Replace(outputStr, "-")

	' chr(150) is like a minus sign but bigger, used sometimes in Winword.
	' That character will give problems so convert it as a minus one
	outputStr=replace(outputStr, chr(150), "-")

	SanitizeFileName = outputStr

End Function

Sub ProcessFile(sSourceFile)

Dim objFileMD, objTmp, objBinaryStream
Dim sFolder, sSourceExt, sTarget
Dim sPandoc, sContent

	Set objFile = objFSO.GetFile(sSourceFile)

	' Folder of the note (f.i. c:\christophe\notes\word)
	sFolder = objFSO.GetParentFolderName(objFile)

	' File name (without path and extension)
	sBaseName = objFSO.GetBaseName(objFile)

	' File's extension (f.i. docx)
	sSourceExt = objFSO.GetExtensionName(objFile)

	' -------------------------------
	' Be sure to have clean filenames
	sNewName = sFolder + "\" + SanitizeFileName(sBaseName) + "." + sSourceExt
	Call objFSO.MoveFile(sSourceFile, sFolder + "\" + SanitizeFileName(sBaseName) + "." + sSourceExt)
	Set objFile = objFSO.GetFile(sNewName)
	sSourceFile=sNewName
	sBaseName = objFSO.GetBaseName(objFile)
	' -------------------------------

	sMediaFolder =replace(SanitizeFileName(sBaseName), " ", "-")

	' Name of the file that will be created by pandoc (f.i. c:\christophe\notes\word\document.md)
	sTarget = replace(objFSO.GetFileName(objFile), sSourceExt, "md")

	'wScript.echo space(3)+"Full name "+sSourceFile
	'wScript.echo space(3)+"Folder "+sFolder
	'wScript.echo space(3)+"Basename "+sBaseName
	'wScript.echo space(3)+"Extension "+sSourceExt
	'wScript.echo space(3)+"Target "+sTarget

	' Create subfolders for images and files
	If Not (objFSO.FolderExists(sFolder + "\.images")) Then objFSO.CreateFolder(sFolder + "\.images")
	If Not (objFSO.FolderExists(sFolder + "\.files"))  Then objFSO.CreateFolder(sFolder + "\.files")

	' Build the pandoc command line
	sPandoc = cPandoc

	' -w indicate the target desired format. Here, convert to a markdown_github format
	sPandoc = sPandoc + " -w markdown_github"

	' Convert accentuated characters in their HTML equivalent (f.i. & will be saved as &amp;)
	sPandoc = sPandoc + " --ascii"

	' Set the heading style to #, ##, ### and not lines (see http://pandoc.org/MANUAL.html#atx-style-headers)
	sPandoc = sPandoc + " --atx-headers"

	' Extract images and saved them in a .images/FILENAME subfolder
	sPandoc = sPandoc + " --extract-media=.images/" + sMediaFolder

	' Output filename
	sPandoc = sPandoc + " -o """ + sTarget + """"

	' And the source filename
	sPandoc = sPandoc + " """ + sBaseName+"."+sSourceExt + """"

	'wScript.echo sPandoc

	' Don't show the window and, important, wait until the pandoc script is finished
	objShell.Run sPandoc, 0, 1

	' Now, read the generated .md file and replace a few things
	If objFSO.FileExists(sFolder + "\" + sTarget) Then

		' Read the file's content
		Set objFileMD = objFSO.OpenTextFile(sTarget,1)
		sContent = objFileMD.ReadAll()
		objFileMD.Close
		Set objFileMD = Nothing

		' Add an intro : a link to the original file
		sContent = "[Link to the original file, before conversion](%URL%.files/" + Replace(sBaseName," ","%20") + "." + sSourceExt + ")" + vbCrLf+ vbCrLf + sContent

		' ---------------------------------------------------------
		'
		' Add the %URL% variable so marknotes will keep absolute filename for images

		sContent = Replace(sContent, "src="+chr(34)+".images/", "src="+chr(34)+"%URL%.images/")

		' Remove non-ASCII characters from the note's content
		' https://stackoverflow.com/a/37025007/1065340
		Set objRegExp = New Regexp
		objRegExp.Global = True
		objRegExp.Pattern = "[^\u0000-\u007F]"
		sContent = objRegExp.Replace(sContent, "")
		Set objRegExp = Nothing

		objFSO.DeleteFile(sTarget)

		Set objBinaryStream = CreateObject("ADODB.Stream")
		objBinaryStream.CharSet = "utf-8"
		objBinaryStream.Open
		objBinaryStream.WriteText Replace(sContent, vbCrLF, vbLF), 0
		objBinaryStream.SaveToFile sTarget, 2
		objBinaryStream.Close
		Set objBinaryStream = Nothing

		' If the .md file is still there, move the original file into the .files subfolder
		If objFSO.FileExists(sTarget) Then
			'If objFSO.FileExists(sFolder+"\.files\"+sBaseName+"."+sSourceExt) Then
			'	objFSO.DeleteFile(sFolder+"\.files\"+sBaseName+"."+sSourceExt)
			'End if
			Call objFSO.MoveFile(sSourceFile, sFolder+"\.files\"+sBaseName+"."+sSourceExt)
		End if

	End if

End Sub

' in_array helper
Function in_array(value, arr)
	in_array = False
	value = trim(value)
	For Each hay in arr
		If trim(hay) = value Then
			in_array = True
			Exit For
		End If
	Next
End Function

' ###############
' # Entry point #
' ###############

	arrSupportedFileExt= array("docx","odt")

	Call  ShowHeader

	Set objShell = CreateObject("WScript.Shell")
	Set objFSO = CreateObject("Scripting.FileSystemObject")

	For Each objFile In objFSO.GetFolder(".").Files
		If (in_array(objFSO.GetExtensionName(objFile.Name), array("docx","odt"))) Then
			WScript.Echo vbCrLf + Space(3) + "Process " + objFile.Name
			ProcessFile(objFSO.GetAbsolutePathName(objFile))
		End if
	Next

	Set objFSO = Nothing
	Set objShell = Nothing
