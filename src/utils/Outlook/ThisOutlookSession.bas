' Code in ThisOutlookSession

Option Explicit

Dim cInspector As clsInspector

' --------------------------------------------------------------------------------
'
' Event gets triggered when you quit Outlook
' Clean up
'
' --------------------------------------------------------------------------------

Private Sub Application_Quit()

   Set cInspector = Nothing

End Sub

' --------------------------------------------------------------------------------
'
' Event gets triggered when you start Outlook
' Initialize class "clsInspector"
'
' --------------------------------------------------------------------------------

Private Sub Application_Startup()

   Set cInspector = New clsInspector

End Sub
