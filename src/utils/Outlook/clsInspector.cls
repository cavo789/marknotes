' -------------------------------------------------------------------------------------
'
' Source : @link : http://www.vbaexpress.com/kb/getarticle.php?kb_id=502
'
' -------------------------------------------------------------------------------------

Option Explicit

Dim WithEvents oAppInspectors As Outlook.Inspectors
Dim WithEvents oMailInspector As Outlook.Inspector
Dim WithEvents oOpenMail As Outlook.MailItem
Dim WithEvents oMarknotesButton As Office.CommandBarButton

Private Sub Class_Initialize()

   ' oAppInspectors Returns a handle to the Inspectors collection

   Set oAppInspectors = Application.Inspectors

End Sub

' --------------------------------------------------------------------------------
'
' Event gets triggered every time a Window or Item is opened in Outlook Interface
' Like: E-mail, Contacts, Tasks
'
' --------------------------------------------------------------------------------

Private Sub oAppInspectors_NewInspector(ByVal Inspector As Inspector)

   If Inspector.CurrentItem.Class <> olMail Then

      ' Only deal with Email Items...else exit

      Exit Sub

   End If

   ' Set a reference to the e-mail to trap the Open event

   Set oOpenMail = Inspector.CurrentItem
   Set oMailInspector = Inspector

End Sub

' --------------------------------------------------------------------------------
'
' Event gets triggered if oOpenMail is opened!
'
' --------------------------------------------------------------------------------

Private Sub oOpenMail_Open(Cancel As Boolean)

   Dim oMailBar As Office.CommandBar

   ' Set a reference to commandbar Standard and add the commandbutton

   Set oMailBar = oMailInspector.CommandBars("Standard")

   ' Clean up left over buttons if any

   Call DeleteButtons(oMailBar)
   Set oMarknotesButton = oMailBar.Controls.Add(Type:=msoControlButton)
   oMailBar.Visible = True

   ' Set properties of commandbutton

   With oMarknotesButton
      .Caption = "Save to marknotes"
      .FaceId = 1000
      .Style = msoButtonIconAndCaption
   End With

   ' Clean up

   Set oMailBar = Nothing

End Sub

' --------------------------------------------------------------------------------
'
' Event gets triggered if oOpenMail is Closed!
'
' --------------------------------------------------------------------------------

Private Sub oOpenMail_Close(Cancel As Boolean)

   Dim oMailBar As Office.CommandBar

   ' Set a reference to commandbar Standard

   Set oMailBar = oMailInspector.CommandBars("Standard")

   Call DeleteButtons(oMailBar)

   ' Clean up

   Set oMailBar = Nothing
   Set oOpenMail = Nothing

End Sub

' --------------------------------------------------------------------------------
'
' Event gets triggered if oMarknotesButton is Clicked!
'
' --------------------------------------------------------------------------------

Private Sub oMarknotesButton_Click(ByVal Ctrl As Office.CommandBarButton, CancelDefault As Boolean)

Dim cMarknotes As clsMarknotes

   Set cMarknotes = New clsMarknotes
   Call cMarknotes.ProcessMail(oOpenMail)
   Set cMarknotes = Nothing

End Sub

' --------------------------------------------------------------------------------
'
' Delete all buttons
'
' --------------------------------------------------------------------------------

Private Sub DeleteButtons(oBar As Office.CommandBar)

   Dim oCtl As Office.CommandBarControl

   On Error Resume Next

   ' Loop through all controls to remove our

   For Each oCtl In oBar.Controls
      If oCtl.Caption = oMarknotesButton.Caption Then
         oCtl.Delete
      End If
   Next

   ' Clean up

   Set oCtl = Nothing

End Sub

' --------------------------------------------------------------------------------
'
' Class Terminate
'
' --------------------------------------------------------------------------------

Private Sub Class_Terminate()

   Set oAppInspectors = Nothing
   Set oOpenMail = Nothing
   Set oMarknotesButton = Nothing

End Sub
 'End of Classmodule: clsInspector
