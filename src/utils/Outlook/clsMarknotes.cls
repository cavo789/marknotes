' ===========================================================================================
'
' Author : Christophe Avonture
' Date   : September 2017
' Aim    : From within Outlook, being able to export the opened mail as a .md file
'          so that email (file) can be viewed from marknotes.
'
'          By saving important emails into marknotes, it makes easier to retrieve
'          information in one central place.
'
' GitHub repo : https://github.com/cavo789/marknotes_outlook/
'
' How to install ?
' ----------------
'
' 1. Start Outlook.
' 2. In Outlook, press ALT-F11 so the Visual Basic Editor (VBE) will be opened.
' 3. You'll see at the top left the explorer pane. Right click under the
'    root entry (first entry in the treeview) and select "Import" from the
'    contextual menu.
' 4. Import every single files of this repository i.e.
'    - Import the class "clsInspector.cls"
'    - Import the class "clsMarknotes.cls"
'    - Import the mode "ThisOutlookSession.bas"
' 5. In the VBE, click on the Tools menu then select References and add a
'    reference to "Microsoft VBScript Regular Expressions 5.5".
' 6. When done save your project (press CTRL-S).
' 7. Quit Outlook and restart it (so the new session environment can be created).
' 8. Open an existing email, you should see a "ADD-INS" entry as explained in the
'    "How to use ?" chapter here below.
'
' How to use ?
' ------------
'
' 1. Open an existing emai
' 2. You'll see a new menu in your ribbon : ADD-INS.
' 3. Click on the menu and you'll be able to see (and click) on "Save to marknotes"
'
' The VBA code will extract the HTML version of the email and will clean (as much
' as possible) the HTML since Outlook add a huge number of mess.
'
' Note : Outlook has the very bad idea to add conditionnal tags before content so
' when this code will remove these tags.
'
' VBA
' ---
'
' Please note that this script requires a reference to the library below.
' Click on Tools - References to be sure that this library is selected (checked)
'
'   * Microsoft VBScript Regular Expressions 5.5 or greater
'
' ===========================================================================================

Option Explicit
Option Base 0
Option Compare Text

Const cOutFolder = "C:\Christophe\Repository\notes\docs\"
Const cAskBeforeSave = True

' Used by GetTempFolderName()
#If Win64 Then
   Private Declare PtrSafe Function GetTempPath Lib "kernel32" Alias "GetTempPathA" _
      (ByVal nBufferLength As Long, ByVal lpBuffer As String) As Long
#Else
   Private Declare Function GetTempPath Lib "kernel32" Alias "GetTempPathA" _
      (ByVal nBufferLength As Long, ByVal lpBuffer As String) As Long
#End If

' Used by Execute()
#If Win64 Then
   Private Declare PtrSafe Function ShellExecute Lib "shell32.dll" Alias "ShellExecuteA" (ByVal hWnd As Long, _
      ByVal Operation As String, ByVal FileName As String, Optional ByVal Parameters As String, _
      Optional ByVal Directory As String, Optional ByVal WindowStyle As Long = vbMinimizedFocus) As Long
#Else
   Private Declare Function ShellExecute Lib "shell32.dll" Alias "ShellExecuteA" (ByVal hWnd As Long, _
      ByVal Operation As String, ByVal FileName As String, Optional ByVal Parameters As String, _
      Optional ByVal Directory As String, Optional ByVal WindowStyle As Long = vbMinimizedFocus) As Long
#End If

' Used by GetFileName()
#If Win64 Then
   Private Declare PtrSafe Function GetOpenFileName Lib "comdlg32.dll" Alias "GetOpenFileNameA" _
      (pOpenfilename As OPENFILENAME) As Long

   Private Type OPENFILENAME
        lStructSize As Long
        hwndOwner As LongPtr
        hInstance As LongPtr
        lpstrFilter As String
        lpstrCustomFilter As String
        nMaxCustFilter As Long
        nFilterIndex As Long
        lpstrFile As String
        nMaxFile As Long
        lpstrFileTitle As String
        nMaxFileTitle As Long
        lpstrInitialDir As String
        lpstrTitle As String
        flags As Long
        nFileOffset As Integer
        nFileExtension As Integer
        lpstrDefExt As String
        lCustData As Long
        lpfnHook As LongPtr
        lpTemplateName As String
    End Type

#Else
   Private Declare Function GetOpenFileName Lib "comdlg32.dll" Alias "GetOpenFileNameA" _
      (pOpenfilename As OPENFILENAME) As Long

   Private Type OPENFILENAME
        lStructSize As Long
        hwndOwner As Long
        hInstance As Long
        lpstrFilter As String
        lpstrCustomFilter As String
        nMaxCustFilter As Long
        nFilterIndex As Long
        lpstrFile As String
        nMaxFile As Long
        lpstrFileTitle As String
        nMaxFileTitle As Long
        lpstrInitialDir As String
        lpstrTitle As String
        flags As Long
        nFileOffset As Integer
        nFileExtension As Integer
        lpstrDefExt As String
        lCustData As Long
        lpfnHook As Long
        lpTemplateName As String
    End Type
#End If

' --------------------------------------------------------------------------------------------------------------
'
' This returns the portion of the string S that is to the left of the first vbNullChar character.
' If vbNullChar is not found, the entire string is returned.
'
' --------------------------------------------------------------------------------------------------------------

Private Function TrimNull(ByVal s As String) As String

   Dim wPos As Integer

   If (s <> vbNullString) Then

      wPos = InStr(1, s, vbNullChar)
      If (wPos > 0) Then s = Left(s, wPos - 1)

   End If

   TrimNull = s

End Function

' ------------------------------------------------------------------------------
'
' Get the fullpath of the temporary folder
'
' Sample code
'
'    sFileName = CAVO_AddIn.cFolder.GetTempFolderName() & "test.xlsx"
'
' will return something like C:\Users\AVONTU~1\AppData\Local\Temp\test.xlsx
'
' ------------------------------------------------------------------------------

Private Function GetTempFolderName() As String

   Dim sResult As String
   Dim wTemp As Integer

   sResult = String(260, " ")

   On Error Resume Next

   wTemp = GetTempPath(260, sResult)
   If Err.Number <> 0 Then sResult = Environ("Temp")

   sResult = TrimNull(sResult)

   On Error GoTo 0

   GetTempFolderName = sResult

End Function

' --------------------------------------------------------------------------------------------------------------
'
' Execute a program.
'
' Parameters :
'
'   Program    : either an executable (like notepad.exe) or a file with an association (f.i. test.txt).  In this case, the
'                associated program will be started.   Can be an url.
'   Parameters : in case of an exe
'   Directory  : starting directory
'
' --------------------------------------------------------------------------------------------------------------

Private Function Execute(ByVal Program As String, Optional ByVal Parameters As String = vbNullString, _
   Optional ByVal Directory As String = vbNullString) As Integer

   Dim wReturn As Long
   Dim Wsh As Object ' New WshShell  ' Requires Windows Script Host Object Model

   ' When ShellExecute returns a value greater than 32, it means that the function is successfull
   wReturn = ShellExecute(0, "open", Program, Parameters, Directory, 1) ' 1 = SW_SHOWNORMAL

   Execute = wReturn

End Function

' ---------------------------------------------------------------------------------------
'
' Create a text file on disk. The Charset should be "ANSI" for Windows platform
' or "UTF-8" for Unix systems
'

' ---------------------------------------------------------------------------------------

Private Sub CreateTextFile(ByVal sFileName As String, ByVal sContent As String, Optional ByVal CharSet = "Ansi")

   Dim objBinaryStream As Object

   If (sFileName = "") Then Exit Sub

   On Error Resume Next

   Set objBinaryStream = CreateObject("ADODB.Stream")

   objBinaryStream.CharSet = CharSet
   objBinaryStream.Type = 2 ' adTypeText
   objBinaryStream.Open

   objBinaryStream.WriteText sContent, 1

   objBinaryStream.SaveToFile sFileName, 2 ' adSaveCreateOverWrite

   objBinaryStream.Close

Err:

   If Err.Number <> 0 Then
      Debug.Print "ERROR in CreateTextFile - " & Err.Description
      Err.Clear
   End If

   On Error GoTo 0

End Sub

' ---------------------------------------------------------------------------------------
'
' Keep only the portion between to body tag
'
' ---------------------------------------------------------------------------------------

Private Function ExtractContentPart(ByVal sHTML As String) As String

   Dim sReturn As String
   Dim matches As Object, match As Object
   Dim regEx As New RegExp

   On Error GoTo Err:

   sReturn = ""

   ' Replace CRLF by LF for Unix style
   sHTML = Replace(sHTML, vbCrLf, "\n")

   With regEx
      .Global = True
      .MultiLine = True
      .IgnoreCase = True
      .Pattern = "\<body[^>]*>([^\n]*)\<\/body>"
   End With

   Set matches = regEx.Execute(sHTML)

   If matches.Count <> 0 Then

      sReturn = matches.Item(0).SubMatches.Item(0)

   End If

Err:

   If Err.Number <> 0 Then
      Debug.Print "ERROR in ExtractContentPart - " & Err.Description
      Err.Clear
      sReturn = ""
   End If

   On Error GoTo 0

   ExtractContentPart = sReturn

End Function

' ---------------------------------------------------------------------------------------
'
' The .HTMLBody returns a lot of message, remove unneeeded tags like heading, div or span
' tags, ...
'
' ---------------------------------------------------------------------------------------

Private Function RemoveUnneededTags(ByVal sHTML As String, ByVal sPattern As String) As String

   Dim sReturn As String
   Dim matches As Object, match As Object
   Dim regEx As New RegExp
   Dim I As Integer

   On Error GoTo Err:

   sReturn = sHTML

   With regEx
      .Global = True
      .MultiLine = True
      .IgnoreCase = True
      .Pattern = sPattern
   End With

   Set matches = regEx.Execute(sHTML)

   If matches.Count <> 0 Then

      I = 0

      For Each match In matches
         sReturn = Replace(sReturn, matches.Item(I), match.SubMatches.Item(0))
         I = I + 1
      Next match

   End If

Err:

   If Err.Number <> 0 Then
      Debug.Print "ERROR in RemoveUnneededTags - " & Err.Description
      Err.Clear
      sReturn = ""
   End If

   On Error GoTo 0

   RemoveUnneededTags = sReturn

End Function

' ---------------------------------------------------------------------------------------
'
' Remove any occurences of "sPattern" (i.e. replace by an empty string)
'
' ---------------------------------------------------------------------------------------

Private Function RemoveMess(ByVal sHTML As String, ByVal sPattern As String) As String

   Dim sReturn As String
   Dim matches As Object
   Dim regEx As New RegExp

   On Error GoTo 0

   sReturn = ""

   With regEx
      .Global = True
      .MultiLine = True
      .IgnoreCase = True
      .Pattern = sPattern
   End With

   ' Remove <o: tags
   sReturn = regEx.Replace(sHTML, "")

Err:

   If Err.Number <> 0 Then
      Debug.Print "ERROR in RemoveMess - " & Err.Description
      Err.Clear
      sReturn = ""
   End If

   On Error GoTo 0

   RemoveMess = sReturn

End Function

Private Function ReplaceTag(ByVal sHTML As String, ByVal sPattern As String, ByVal sReplace As String) As String

   Dim sReturn As String, sValue As String
   Dim matches As Object, match As Object
   Dim regEx As New RegExp
   Dim I As Integer

   On Error GoTo Err:

   sReturn = sHTML

   With regEx
      .Global = True
      .MultiLine = True
      .IgnoreCase = True
      .Pattern = sPattern
   End With

   Set matches = regEx.Execute(sHTML)

   If matches.Count <> 0 Then

      I = 0

      For Each match In matches

         If (match.SubMatches.Count > 0) Then
            sValue = Replace(sReplace, "$1", match.SubMatches.Item(0))
         Else
            sValue = Replace(sReplace, "$1", "")
         End If

         sReturn = Replace(sReturn, matches.Item(I), sValue)

         I = I + 1

      Next match

   End If

Err:

   If Err.Number <> 0 Then
      Debug.Print "ERROR in ReplaceTag - " & Err.Description
      Err.Clear
      sReturn = ""
   End If

   On Error GoTo 0

   ReplaceTag = sReturn

End Function

Private Function CleanBulletList(ByVal sHTML As String) As String

   'sContent = Replace(sContent, "Â·", "* ")
   'sContent = Replace(sContent, "^\.\s*", "* ")

   Dim sReturn As String
   Dim matches As Object, match As Object
   Dim regEx As New RegExp
   Dim I As Integer, J As Byte, K As Byte
   Dim arr As Variant

   On Error GoTo Err:

   sReturn = sHTML

   arr = Array("^\·\s*(.*)$")

   K = UBound(arr)

   For J = 0 To K

      With regEx
         .Global = True
         .MultiLine = True
         .IgnoreCase = True
         .Pattern = arr(J)
      End With

      Set matches = regEx.Execute(sHTML)

      If matches.Count <> 0 Then

         I = 0

         For Each match In matches

            sReturn = Replace(sReturn, matches.Item(I), "* " & match.SubMatches.Item(0))

            I = I + 1

         Next match

      End If

   Next J

Err:

   If Err.Number <> 0 Then
      Debug.Print "ERROR in CleanBulletList - " & Err.Description
      Err.Clear
      sReturn = ""
   End If

   On Error GoTo 0

   CleanBulletList = sReturn

End Function

' ---------------------------------------------------------------------------------------
'
' Remove any accentuated characters, dot, space, comma, ... and replace them by
' an underscore character
'
' ---------------------------------------------------------------------------------------

Private Function Slugify(ByVal sText As String) As String

   Dim sReturn As String, sValue As String
   Dim matches As Object, match As Object
   Dim regEx As New RegExp
   Dim I As Integer

   On Error GoTo Err:

   sReturn = sText

   With regEx
      .Global = True
      .MultiLine = True
      .IgnoreCase = True
      .Pattern = "[^\w\.@-]"
   End With

   sReturn = regEx.Replace(sText, "_")

Err:

   If Err.Number <> 0 Then
      Debug.Print "ERROR in Slugify - " & Err.Description
      Err.Clear
      sReturn = ""
   End If

   On Error GoTo 0

   Slugify = sReturn

End Function

' ---------------------------------------------------------------------------------------
'
' Display a "File save as" dialog to allow the user to select where to save the file
'
' @link : http://www.jkp-ads.com/articles/apideclarations.asp
'
' ---------------------------------------------------------------------------------------

Private Function GetFileName(ByVal sDefaultFileName As String) As String

   Dim objFSO As Object
   Dim OpenFile As OPENFILENAME
   Dim lReturn As Long
   Dim sReturn As String, sFolderName As String

   On Error GoTo Err:

   sFolderName = "C:\"

   If (sDefaultFileName <> "") Then
      Set objFSO = CreateObject("Scripting.FileSystemObject")
      sFolderName = objFSO.GetParentFolderName(sDefaultFileName)
      sDefaultFileName = objFSO.GetBaseName(sDefaultFileName) & "." & objFSO.GetExtensionName(sDefaultFileName)
      Set objFSO = Nothing
   End If

   OpenFile.lpstrFilter = "*.md"
   OpenFile.nFilterIndex = 1
   OpenFile.hwndOwner = 0
   OpenFile.lpstrFile = sDefaultFileName & String(257 - Len(sDefaultFileName), 0)

   #If Win64 Then
       OpenFile.nMaxFile = LenB(OpenFile.lpstrFile) - 1
       OpenFile.lStructSize = LenB(OpenFile)
   #Else
       OpenFile.nMaxFile = Len(OpenFile.lpstrFile) - 1
       OpenFile.lStructSize = Len(OpenFile)
   #End If

   OpenFile.lpstrFileTitle = OpenFile.lpstrFile
   OpenFile.nMaxFileTitle = OpenFile.nMaxFile
   OpenFile.lpstrInitialDir = sFolderName
   OpenFile.lpstrTitle = "Marknotes"
   OpenFile.flags = 0
   lReturn = GetOpenFileName(OpenFile)

   If lReturn = 0 Then
      sReturn = ""
   Else
      sReturn = Trim(Left(OpenFile.lpstrFile, InStr(1, OpenFile.lpstrFile, vbNullChar) - 1))
   End If

Err:

   If Err.Number <> 0 Then
      Debug.Print "ERROR in GetFileName - " & Err.Description
      Err.Clear
      sReturn = ""
   End If

   On Error GoTo 0

   GetFileName = sReturn

End Function

Private Sub Die(ByVal sMessage As String)

   Debug.Print sMessage
   End

End Sub

' ---------------------------------------------------------------------------------------
' -                                                                                     -
' - Entry point, a button in the Outlook interface can be added to fire this subroutine -
' -                                                                                     -
' ---------------------------------------------------------------------------------------

Public Sub ProcessMail(ByRef omail As MailItem)

   Dim sContent As String, sFolderName As String, sFileName As String, sTemp As String
   Dim sTag As String, sPattern As String
   Dim wPos As Long
   Dim wOld As Long
   Dim arr As Variant
   Dim I As Byte, J As Byte
   Dim bOpenFile As Boolean, bResult As Boolean

   ' Retrieve the HTML content
   ' But don't take just the mail's content but simulate a reply
   ' This way, we've the name of the sender, who was in the recipients list, the received date time, ...

   sContent = omail.Reply.HTMLBody
   If (Trim(sContent) = "") Then Call Die("No content in the mail - oMail.Reply.HTMLBody")

   ' Once the mail.HTMLBody has been extracted, analyze the HTML and keep only the portion
   ' of text in the <body> ... </body> tag (not included) so; keep only the message, not any
   ' declarations added by Outlook

   sContent = ExtractContentPart(sContent)
   If (Trim(sContent) = "") Then Call Die("No content in the mail - ExtractContentPart")

   ' Remove unneeded DIV tags

   sContent = RemoveUnneededTags(sContent, "\<div class=WordSection\d*>(.*)\<\/div>")
   If (Trim(sContent) = "") Then Call Die("No content in the mail - RemoveUnneededTags WordSection")

   ' ----------------------------------
   '
   ' Remove mess

   ' Remove any office document tags i.e. tags with a letter followed by a ":" sign like "<o:" or "<v:" or ...
   ' <p class=MsoNormal>Title<o:p></o:p></p><p class=MsoNormal>A second line<o:p></o:p></p>
   ' and keep only <p>Title</p><p>A second line</p>

   sTemp = ""
   While (sTemp <> sContent)
      sTemp = sContent
      sContent = RemoveMess(sContent, "<\/?[a-z]+:[^>]*>[^>]*>")
   Wend

   If (Trim(sContent) = "") Then Call Die("No content in the mail - RemoveMess OpenOffice Tags")

   ' Replace special characters

   sContent = Replace(sContent, "&nbsp;", " ")
   sContent = Replace(sContent, "“", "`")
   sContent = Replace(sContent, "”", "`")
   sContent = Replace(sContent, "‘", "`")
   sContent = Replace(sContent, "’", "`")
   sContent = Replace(sContent, "«", "`")
   sContent = Replace(sContent, "»", "`")

   sContent = Replace(sContent, "<![if !supportLists]>* &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <![endif]>", "* ")

   sContent = RemoveMess(sContent, " class=MsoNormal")
   If (Trim(sContent) = "") Then Call Die("No content in the mail - RemoveMess class=MsoNormal")

   ' Remove empty tags
   sContent = RemoveMess(sContent, "<[^\/>][^>]*><\/[^>]+>")
   If (Trim(sContent) = "") Then Call Die("No content in the mail - RemoveMess Empty Tags")

   ' Remove images with an "intern" content i.e. with src="cid:
   sContent = RemoveMess(sContent, "<img[^>]*src=""cid:[^>]*>")
   If (Trim(sContent) = "") Then Call Die("No content in the mail - RemoveMess Intern images")


   ' ------------------------------------
   '
   ' Outlook is putting a lot of things into comments so, removing comments can also remove content
   ' but, in fine, even if we don't remove the conditionnal here, it won't be displayed in the .md
   ' file since this is comments.
   '
   ' Remove HTML comments
   sContent = RemoveMess(sContent, "<!--[\s\S]*?-->")
   If (Trim(sContent) = "") Then Call Die("No content in the mail - RemoveMess HTML Comments")

   ' Remove conditionnal tags
   sContent = RemoveMess(sContent, "<!--\[if.*\[endif\] *>")
   If (Trim(sContent) = "") Then Call Die("No content in the mail - RemoveMess Conditionnal Tags")
   '
   ' ------------------------------------

   ' ------------------------------------
   ' Remove unneeded span and div tags
   ' Sometimes, poor quality, Outlook don't add the ending tag (<span> without </span>)

   arr = Array( _
      Array("<div", "\<div[^>]*>(.*?)(<\/div>)?", "</div>"), _
      Array("<span", "\<span[^>]*>(.*?)(<\/span>)?", "</span>"), _
      Array("<![if", "\<\!\[if[^>]*>(.*?)(<\!\[endif\]>)?", "<![endif]>") _
   )

   J = UBound(arr(0))

   For I = 0 To J

      sTag = arr(I)(0)
      sPattern = arr(I)(1)

      wPos = InStr(1, sContent, sTag)

      While (wPos <> 0)

         sContent = RemoveUnneededTags(sContent, sPattern)
         If (Trim(sContent) = "") Then Call Die("No content in the mail - RemoveUnneedTags " & sTag)

         wPos = InStr(1, sContent, sTag)

      Wend

      ' In case of orphan end tag (like '</span>'), remove it

      sContent = Replace(sContent, arr(I)(2), "")

   Next

   ' Remove mail signature (the signature is inside a <a name="_MailAutoSig">.....</a> bloc

   'sContent = ReplaceTag(sContent, "\<a name=""_MailAutoSig"">(.*?)<\/a>", "")
   'If (Trim(sContent) = "") Then Call Die("No content in the mail - ReplaceTag MailAutoSig")

   ' Replace <p>...</p> by ... followed by a two line feed

   sContent = ReplaceTag(sContent, "\<p[^>]*>(.*?)<\/p>", "$1" & Chr(10) & Chr(10))
   If (Trim(sContent) = "") Then Call Die("No content in the mail - ReplaceTag Paragraph")

   ' Replace <br> by one linefeed

   sContent = ReplaceTag(sContent, "\<br\>", Chr(10) & Chr(10))
   If (Trim(sContent) = "") Then Call Die("No content in the mail - ReplaceTag BR")

   ' Replace <i>...</i> by *...*

   sContent = ReplaceTag(sContent, "\<i[^>]*>(.*?)<\/i>", "*$1*")
   If (Trim(sContent) = "") Then Call Die("No content in the mail - ReplaceTag Italic")

   ' Replace <b>...</b> by **...**

   sContent = ReplaceTag(sContent, "\<b[^>]*>(.*?)<\/b>", "**$1**")
   If (Trim(sContent) = "") Then Call Die("No content in the mail - ReplaceTag Bold")

   ' Bullets
   sContent = CleanBulletList(sContent)
   If (Trim(sContent) = "") Then Call Die("No content in the mail - CleanBulletList")

   ' No need to have multiple empty lines
   sContent = ReplaceTag(sContent, "\n{3,}", Chr(10) & Chr(10))
   If (Trim(sContent) = "") Then Call Die("No content in the mail - ReplaceTag Multiple LF")

   sContent = Trim(sContent)

   ' And, finally, add title and YAML

   sContent = Chr(10) & _
      "# " & omail.Subject & Chr(10) & Chr(10) & _
      ">" & omail.ReceivedTime & Chr(10) & Chr(10) & _
      sContent

   ' ------------------------------------------------------------------------
   '
   ' Create the file

   ' Derive the filename

   sFolderName = cOutFolder
   If (Dir(sFolderName) = "") Then
      sFolderName = GetTempFolderName()
   End If

   sFileName = sFolderName & Slugify(omail.Subject) & ".md"

   If (cAskBeforeSave) Then
      sFileName = GetFileName(sFileName)
   End If

   If (Trim(sFileName) <> "") Then

      Call CreateTextFile(sFileName, sContent, "utf-8")

      bOpenFile = (MsgBox("File " & sFileName & " created." & vbCrLf & vbCrLf & _
         "Click on Yes if you want to open that file", vbYesNo, "Marknotes") = vbYes)

      If bOpenFile Then

         ' Execute the command; if not found, return 2 (ERROR_FILE_NOT_FOUND);
         ' If the function succeeds, it returns a value greater than 32.
         bResult = (Execute("notepad++.exe", sFileName) > 32)

         If Not bResult Then
            bResult = (Execute("notepad.exe", sFileName) = 0)
         End If

      End If

   End If

End Sub
