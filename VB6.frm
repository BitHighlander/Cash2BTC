***********************************************************************
'*      Pyramid Technologies, Inc.  RS-232 Interface Program      *
'*               Copyright 2002 Pyramid Technologies, Inc.             *
'***********************************************************************
'If you have purchased PTI Bill Acceptors, we hope that you
'can use this source code to assist you with your kiosk or vending
'application, and that it is a positive and profitable experience for you.
'
'You may use and integrate this source code freely, provided
'the terms and conditions are adhered to.
'By using this software, you agree to the following terms and conditions:
'
' 1.  This software is provided "as is" to the user.  PTI assumes no
' responsibility for any damages which may result from the use or misuse
' of this software.  The user is entirely responsible for any consequences
' resulting from the integration of this source code into the user's system.
'
' 2.  Although PTI will likely choose to provide technical support for the
' use of this source code, PTI is not obligated to do so.
'
' 3.  This source code may not be re-distributed or published without expressed,
' written permission from Pyramid Technologies, Inc.
'
' 4.  This copyright notice and agreement must not be deleted from the source
' code if any or all of PTI's source code is integrated into the user's application.
'
' 5.  Permission to use this software will be revoked if it is used in a way
' that is deemed damaging to PTI, or used for purposes which are illegal
' or damaging to others, or otherwise not representing the intended, proper
' use it was designed for.
'***********************************************************************
'
'Changes to v. 1.30:
'No functional changes - just some modification to wording and appearance.
'
'Changes to v. 1.20:
'Support for extra com ports has been provided (up to 6). Note that choosing
'a port which is not valid on the computer will result in program error.
'
'This new version has the ability to turn "Escrow Mode" on or off.
'Escrow Mode ON: Acceptor waits for escrow message from host to stack
'or return a bill which is in escrow.
'Escrow Mode OFF: Acceptor will stack an escrowed bill without any message
'from the host, and then report the credit.
'
'Previous versions of firmware only supported Escrow Mode ON, and older
'versions of this demo program automatically sent an escrow "STACK" instruction
'when the bill acceptor reported an escrowed bill. Newer versions of the
'firmware support either mode, in order to work with a handful of host systems
'which turn off the Escrow Mode. With this program, if the Escrow Mode checkbox
'remained checked, this software will continue to function as it did previously.
'If unchecked, the acceptor will automatically stack/accept an escrowed bill without
'a host message. It will not appear much different to the user, but this
'function is provided for diagnostic purposes, or to make it easier should
'the user want to incorporate special functions with Escrow Mode turned OFF.
'If that is the case,
'
'
'
'Overview:
'
'This software provides a framework for interfacing a Windows PC
'to a Pyramid Bill Acceptor configured for RS-232.  It is
'intended for users who want to get up and running quickly with
'kiosk or other PC applications requiring a bill acceptor interface.
'
'This application (as provided by PTI) contains fully functional
'serial communications.  The information received from the bill
'acceptor is translated into an organized set of global variables.
'Simply monitor the states of these variables to determine what
'events have taken place with the bill acceptor, and then take
'whatever action is appropriate for your system. Similarly, a set
'of global variables controls the messages sent by the PC to the
'bill acceptor. This simplification allows you to focus on your
'kiosk application without concern for the low-level routines
'which carry out the details of the RS-232 Interface.
'
'Although the low-level communication code is written for you,
'it is still important to understand what the RS232 interface
'does.  A basic understanding of states, events, and the polling
'process will help you integrate this code into your end application.
'The RS232 Interface Specification is located online at:
'http://www.pyramidacceptors.com/files/RS_232.pdf

'************************************************************
'*               Host PC Global Variables                   *
'************************************************************
Public SetControls As Byte
'Default [1], "Settings and Controls" frame active (and visible).
'Set to [0] if you create your own control functions and wish to
'disable the ones provided (and make them invisible).

Public SetDisplay As Byte
'Default [1], "Monitor" frame active (and visible).
'Set to [0] to turn off the running display which updates with each
'communication exchange with the Acceptor.

Public PortSelect As Byte
'Com 1: [1]      Com 2: [2]        default=1
'(controlled by the radio buttons in the blue box)

Public PollRate As Integer
'Any value between 100 and 5000.  This is the interval (in milliseconds)
'between each serial message.  Most applications use 100 ms for optimal
'response, and 100 is the default.
'(controlled by the horizontal scroll bar in the red box)

Public StartStop As Byte
'Set to [1] when actively communicating, [0] when halted
'(controlled by the start/stop button)


'************************************************************
'* Master (output) Global Variables (PC to Acceptor)        *
'************************************************************
Dim Bill(1 To 7) As Byte
'When a bill is checked/enabled, the corresponding variable is
'set to [1].  When unchecked/disabled, it is set to [0].
'(controlled by the check-boxes in the green box)
'Example: when the Bill 2 checkbox is unchecked (disabled),
'Bill(2) is set to a value of [0].

Public Allow As Byte    'default [1]
'When the Acceptor deterimines a note to be valid, it responds with an Escrow message to the PC.
'When the Escrow message is received, the "Escrowed" variable is set to [1]
'and the "Credit" variable will contain the value of the bill (see next section).
'If "Allow" is set to [0], the next message from the PC will instruct the Acceptor to reject the bill.
'If "Allow" is set to [1], the next message from the PC will instruct the Acceptor to stack the bill.
'If "Allow" is set to [2], the PC will not instruct the Acceptor either way, and the bill will remain in
'escrow until further instruction is received.
'Therefore, if the user requires the ability to prevent escrowed bills from being stacked,
'the user must use the Allow variable accordingly.  Otherwise, the value will always be [1],
'and all escrowed bills will be stacked.  If the host system needs time to make a decision
'about the bill, the variable should be set to [2] until the decision is made.


'************************************************************
'* Acceptor Global Variables (Results of Response to PC)    *
'************************************************************
'The following states are true if the value is [1], otherwise [0]:
Public Idling As Byte       'Acceptor is idling (waiting)
Public Accepting As Byte    'Acceptor pulling in new bill
Public Escrowed As Byte     'Bill is stopped in escrow
Public Stacking As Byte     'Valid bill feeding into cassette (or to rear for stackerless)
Public Stacked As Byte      'Valid bill now in cassette (or cleared back of unit for stackerless)
Public Returning As Byte    'Bill is being returned to the user
Public Returned As Byte     'Bill has finished being returned to the user
Public Cheated As Byte      'Acceptor suspects cheating
Public Rejected As Byte     'Bill was rejected
Public Jammed As Byte       'Bill is jammed in Acceptor
Public Full As Byte         'Cassette is full (must be emptied)
Public Cassette As Byte     'Cassette is present
Public Power As Byte        'Acceptor is initializing (powering up)
Public Invalid As Byte      'Acceptor received invalid command from PC
Public Failure As Byte      'Acceptor has experienced a system failure

'************************************************************
'*           Credit (Vend) Global Variables                 *
'************************************************************
Public Credit As Byte
'When no bill is being credited, this will be [0].
'When a bill is validated by the Acceptor, this variable will hold a
'value from [1] to [7] to indicate which bill was accepted (and
'the PC should issue proper credit accordingly).
'Note:  This variable will only be active for one communication cycle.

Public LastCredit As Byte
'Since the Credit variable is only active (nonzero) for one communication
'cycle, this variable is provided as a backup copy of the credit value [1 to 7]
'to indicate the value of the most recent stacked bill.  This variable will only
'change if a new bill with a different value is stacked.

'************************************************************
'*                RS-232 Globals                            *
'************************************************************
'These RS-232 globals are used by the "Communicate" function which performs
'the serial communications.  In other words, pay no attention to them (but
'they must remain here).
Dim Master(1 To 8) As Byte      'Outgoing data string
Dim Slave(1 To 11) As Byte      'Incoming data string
Public Toggle As Byte           '0 or 1 alternating "Ack" value
Dim Csum As Byte                'Checksum accumulator
Public NRFlag                   'Error flag
Dim ComArray() As Byte          'Comm input
Public T1 As Double             'Time variable
Public Sendit As String         'Comm output
Public Delay As Byte            'credit debouncer


Private Sub Form_Load()
'This function runs only when the program is started.
'Initialization of Global variables:
    SetControls = 1         'enable PTI control frame
    SetDisplay = 1          'enable PTI monitor frame
    PortSelect = 1          'default com 1
    PollRate = 100          'default 100 ms
    Bill(1) = 1             'Bill #1 enabled (default state)
    Bill(2) = 1             'Bill #2 enabled (default state)
    Bill(3) = 1             'Bill #3 enabled (default state)
    Bill(4) = 1             'Bill #4 enabled (default state)
    Bill(5) = 1             'Bill #5 enabled (default state)
    Bill(6) = 1             'Bill #6 enabled (default state)
    Bill(7) = 1             'Bill #7 enabled (default state)
    StartStop = 0           'begin in halted state until START button pressed
    ClearApex               'Clear variables which track the acceptor's responses to the PC
    Allow = 1               'Stack all escrowed bills as default
'Clear Bill Counters
    For ct = 1 To 7
        txBillCount(ct).Text = ""
    Next ct
'Set up Communication Port
    MSComm1.CommPort = 1
    MSComm1.Settings = "9600, E, 7, 1"
    MSComm1.InputLen = 0
    MSComm1.DTREnable = True
    MSComm1.RTSEnable = True
    MSComm1.InputMode = comInputModeBinary
    MSComm1.SThreshold = 1
    MSComm1.RThreshold = 10
End Sub


Private Sub Image1_Click()

End Sub

Private Sub Timer1_Timer()
'This the "main loop" of the program which executes automatically at the rate
'determined by the PollRate variable.

'When in the halted state (at startup, or when the STOP button pressed), this
'timed function does little other than to wait for the START button to be pressed.

'When in the running state (once the START button is pressed), the "Communicate"
'function is called to perform one communication cycle;  The PC sends a poll message
'to the acceptor, and the acceptor should then respond with status information.  Also, the
'"Monitor" function is called to display the information received by the Acceptor.

'Note that the PollRate, SetControls, and SetDisplay, etc., are interpreted
'with every execution of this Timer function.  This allows the program to change its
'behavior "on the fly" to respond to changes from the basic provided controls, or any
'user controls which may be added to replace the provided controls.

    Timer1.Interval = PollRate  'update poll rate
    If SetControls = 1 Then frmControls.Visible = True Else frmControls.Visible = False
    If SetDisplay = 1 Then frmMonitor.Visible = True Else frmMonitor.Visible = False
    If StartStop = 1 Then   'Check if running or halted
        If MSComm1.PortOpen = False Then
            MSComm1.CommPort = PortSelect   'set com port value to equal the selection
            MSComm1.PortOpen = True         'open the port
        End If
        Communicate     'Execute communication routine if "running"
        If SetDisplay = 1 Then
            Monitor     'Update the display if display mode set active
        End If
    End If
    If StartStop = 0 Then
        If MSComm1.PortOpen = True Then MSComm1.PortOpen = False  'Close port when stopped
    End If
End Sub

Private Sub cmdStartStop_Click()
'Activated when the Start/Stop button pressed; toggles run/stop states
    If StartStop = 0 Then
        StartStop = 1       'toggle to "running" state
        cmdStartStop.Caption = "STOP"      'stop/start button text
        cmdStartStop.BackColor = RGB(255, 0, 0)
        SClear  'clear any previous command strings from display
        optCom1.Visible = False     'disallow com port changes while port open
        optCom2.Visible = False
        ClearApex
        MonitorClear    'clear acceptor status information
        GoTo ss_end
    End If
    If StartStop = 1 Then
        StartStop = 0       'toggle to "halted" state
        cmdStartStop.Caption = "START"      'stop/start button text
        cmdStartStop.BackColor = RGB(0, 255, 0)
        optCom1.Visible = True     'disallow com port changes while port closed
        optCom2.Visible = True
    End If
ss_end:
End Sub

Private Sub chkBill_Click(Index As Integer)
'chkBill(0) through chkBill(6) are the check boxes for enabling or disabling
'bills #1 through #7.  When one of these check boxes changes state, the
'corresponding element of array Bill(1 to 7) changes to [1] for checked/enabled,
'or [0] for unchecked/disabled.
    If Bill(Index + 1) = 0 Then
        Bill(Index + 1) = 1
        GoTo chkb_end
    End If
    If Bill(Index + 1) = 1 Then Bill(Index + 1) = 0
chkb_end:
End Sub

Private Sub hsPoll_Change()
'This routine is activated when the poll rate scrollbar is used.
    txtPoll.Text = Str(hsPoll.Value) + " ms"    'display new poll rate
    PollRate = hsPoll.Value         'change value of global variable
End Sub

Private Sub optCom1_Click()
'activated when "Com 1" radio button selected
    PortSelect = 1      'set global variable to indicate com port
End Sub

Private Sub optCom2_Click()
'activated when "Com 2" radio button selected
    PortSelect = 2      'set global variable to indicate com port
End Sub

Private Sub optCom3_Click()
'activated when "Com 3" radio button selected
    PortSelect = 3      'set global variable to indicate com port
End Sub

Private Sub optCom4_Click()
'activated when "Com 4" radio button selected
    PortSelect = 4      'set global variable to indicate com port
End Sub

Private Sub optCom5_Click()
'activated when "Com 5" radio button selected
    PortSelect = 5      'set global variable to indicate com port
End Sub

Private Sub optCom6_Click()
'activated when "Com 6" radio button selected
    PortSelect = 6      'set global variable to indicate com port
End Sub

Private Sub optCom7_Click()
'activated when "Com 7" radio button selected
    PortSelect = 7      'set global variable to indicate com port
End Sub

Private Sub optCom8_Click()
'activated when "Com 8" radio button selected
    PortSelect = 8      'set global variable to indicate com port
End Sub
Private Sub SClear()
'This simply clears the green and blue boxes which show the running hex
'values of the communication strings being sent to and from the Acceptor
    For ctr = 0 To 5
        lbIN(ctr).Caption = ""      'Clear output string displays
        lbOUT(ctr).Caption = ""     'Clear input string display
    Next ctr
End Sub

Private Sub ClearApex()
'Resets the variables which indicate the current state of the acceptor.
'This should be executed when a new communication session is opened.
    Credit = 0:    LastCredit = 0:    Idling = 0: Accepting = 0:    Escrowed = 0
    Stacking = 0: Stacked = 0: Returning = 0:   Returned = 0: Cheated = 0
    Rejected = 0: Jammed = 0: Full = 0: Cassette = 0: Power = 0: Invalid = 0: Failure = 0
End Sub

Private Sub MonitorClear()
'This function resets the information on the display, and is called when
'transitioning from the halted state to a new running state.
    For ct = 1 To 7
        lbD(ct).Visible = False     'Bill Credit labels
        txBillCount(ct).Text = ""   'Bill Count Totals
    Next ct
    For ct = 0 To 6
        lb0(ct).Visible = False     'Labels for info from data byte 0
    Next ct
    For ct = 0 To 4
        lb1(ct).Visible = False     'Labels for info from data byte 1
    Next ct
    For ct = 0 To 2
        lb2(ct).Visible = False     'Labels for info from data byte 2
    Next ct
End Sub

Private Sub Alternate()
'Used by the Communicate function, this simply inverts the ACK number which
'alternates between 1 and 0 with each communication cycle
    If Toggle = 0 Then
        Toggle = 1
        GoTo tgskip
    End If
    If Toggle = 1 Then Toggle = 0
tgskip:
End Sub

Private Sub Communicate()
'This function should not require any user alteration.  Changing this function could
'cause the RS-232 communications to function improperly.
    On Error Resume Next
'set up output
    Master(1) = 2                   'STX
    Master(2) = 8                   'Length
    Master(3) = &H10& Or Toggle     'MSG type and ACK number
    Master(4) = 0                   'Bill Enable/Disable
    If Bill(1) = 1 Then Master(4) = Master(4) Or 1
    If Bill(2) = 1 Then Master(4) = Master(4) Or 2
    If Bill(3) = 1 Then Master(4) = Master(4) Or 4
    If Bill(4) = 1 Then Master(4) = Master(4) Or 8
    If Bill(5) = 1 Then Master(4) = Master(4) Or 16
    If Bill(6) = 1 Then Master(4) = Master(4) Or 32
    If Bill(7) = 1 Then Master(4) = Master(4) Or 64
    
    If Check1.Value = Checked Then Master(5) = &H10& 'S/R, bits 5 and 6 are the stack and return bits
    If Check1.Value = Unchecked Then Master(5) = 0
    If Escrowed = 1 Then            'if bill held in escrow, check the Allow variable
        If Allow = 0 Then Master(5) = Master(5) Or &H40&    'Disallow escrowed bill; set return bit
        If Allow = 1 Then Master(5) = Master(5) Or &H20&    'Allow escrowed bill; set stack bit
    End If
    Master(6) = 0                   'Reserved byte
    Master(7) = 3                   'ETX
'calculate checksum for last byte
    Csum = Master(2) Xor Master(3)
    Csum = Csum Xor Master(4)
    Csum = Csum Xor Master(5)
    Master(8) = Csum Xor Master(6)  'Store csum into byte 8
    Sendit = ""                     'Clear output string
    For ct = 1 To 8
        Sendit = Sendit + Chr(Master(ct))      'Set output string
    Next ct
    MSComm1.Output = Sendit         'Send message to Acceptor

'Receive RS-232 response from Acceptor
   T1 = Timer()
   Do Until Timer() > T1 + 0.4                 'Timeout if no Acceptor Response
        If MSComm1.InBufferCount = 11 Then      'Response always 11 bytes
            ComArray() = MSComm1.Input              'Read Port
            MSComm1.InBufferCount = 0
            For ct = 0 To 10
                Slave(ct + 1) = ComArray(ct) 'Copy data to input array
            Next ct
            NRFlag = 0  'clear error flag and exit loop, proper response received
            Exit Do
        End If
        NRFlag = 1      'set error flag, did not receive proper response
   Loop

'Run some checks on the data received to verify it's valid
    Csum = Slave(2) Xor Slave(3)          'verify checksum
    For ct = 4 To 9
        Csum = Csum Xor Slave(ct)
    Next ct
    If Csum <> Slave(11) Then NRFlag = 2    'indicate checksum error
    If NRFlag <> 0 Then
        Alternate       'because of failure, next MSG from PC must have same ack number, so flip 2x
        GoTo inbad      'Do not interpret "bad" data
    End If

'Interpret response from Acceptor, record the states to the global variables
    If Slave(4) And 1 Then Idling = 1 Else Idling = 0
    If Slave(4) And 2 Then Accepting = 1 Else Accepting = 0
    If Slave(4) And 4 Then Escrowed = 1 Else Escrowed = 0
    If Slave(4) And 8 Then Stacking = 1 Else Stacking = 0
    If Slave(4) And &H10 Then Stacked = 1 Else Stacked = 0
    If Slave(4) And &H20 Then Returning = 1 Else Returning = 0
    If Slave(4) And &H40 Then Returned = 1 Else Returned = 0
    If Slave(5) And 1 Then Cheated = 1 Else Cheated = 0
    If Slave(5) And 2 Then Rejected = 1 Else Rejected = 0
    If Slave(5) And 4 Then Jammed = 1 Else Jammed = 0
    If Slave(5) And 8 Then Full = 1 Else Full = 0
    If Slave(5) And &H10 Then Cassette = 1 Else Cassette = 0
    If Slave(6) And 1 Then Power = 1 Else Power = 0
    If Slave(6) And 2 Then Invalid = 1 Else Invalid = 0
    If Slave(6) And 4 Then Failure = 1 Else Failure = 0
    Dim TempC As Byte
    TempC = Slave(6) And &H38
    If TempC = 0 Then Credit = 0
    If TempC = 8 Then Credit = 1
    If TempC = &H10 Then Credit = 2
    If TempC = &H18 Then Credit = 3
    If TempC = &H20 Then Credit = 4
    If TempC = &H28 Then Credit = 5
    If TempC = &H30 Then Credit = 6
    If TempC = &H38 Then Credit = 7
    If Credit > 0 Then LastCredit = Credit
    
inbad:
   Alternate              'Change toggle value for next time
End Sub

Private Sub Monitor()
'This function should not require any user alteration unless changes are desired
'in the way the information is displayed on the screen.
    For shift = 4 To 0 Step -1  'Shift previous hex strings up on the display
        lbOUT(shift + 1).Caption = lbOUT(shift).Caption
        lbIN(shift + 1).Caption = lbIN(shift).Caption
    Next shift
    om$ = ""
    For ct = 1 To 8     'display output string
        oc$ = Hex$(Master(ct))
        If Len(oc$) = 1 Then oc$ = "0" + oc$
        om$ = om$ + oc$ + " "
    Next ct
    lbOUT(0) = om$
    os$ = ""
    For ct = 1 To 11     'display input string
        oc$ = Hex$(Slave(ct))
        If Len(oc$) = 1 Then oc$ = "0" + oc$
        os$ = os$ + oc$ + " "
    Next ct
    lbIN(0) = os$
'If communication error flag was set, indicate error message
    If NRFlag = 1 Then lbIN(0) = "Acceptor Busy / Not Connected"    'display only error message
    If NRFlag = 2 Then lbIN(0) = "Checksum Error"   'display data + error message
'This routine interprets the global variables as set by the communication routine
'and updates the display information to indicate the current state of the Acceptor
    For ct = 1 To 7         'Clear any invalid bill credit labels
        If ct <> LastCredit Then lbD(ct).Visible = False
        If ct = LastCredit Then lbD(ct).Visible = True
    Next ct
'Set darker color to credit label for residual display (useful during
'acceptance testing, last credited bill will remain on screen with dim color)
    If LastCredit > 0 Then lbD(LastCredit).ForeColor = RGB(150, 0, 150)
'If bill just credited last cycle, override the dark color with bright color
'(if the user is watching the screen, the bright flash will confirm a newly-credited note)
    If Credit > 0 And Stacked = 1 Then
        lbD(Credit).ForeColor = RGB(255, 192, 255)  'flash bright
        If Delay > 4 Then
            txBillCount(Credit).Text = Val(Trim(txBillCount(Credit).Text)) + 1  'update count
            Delay = 0
        End If
    End If
'Display active acceptor states
    If Idling = 1 Then lb0(0).Visible = True Else lb0(0).Visible = False
    If Accepting = 1 Then lb0(1).Visible = True Else lb0(1).Visible = False
    If Escrowed = 1 Then lb0(2).Visible = True Else lb0(2).Visible = False
    If Stacking = 1 Then lb0(3).Visible = True Else lb0(3).Visible = False
    If Stacked = 1 Then lb0(4).Visible = True Else lb0(4).Visible = False
    If Returning = 1 Then lb0(5).Visible = True Else lb0(5).Visible = False
    If Returned = 1 Then lb0(6).Visible = True Else lb0(6).Visible = False
    If Cheated = 1 Then lb1(0).Visible = True Else lb1(0).Visible = False
    If Rejected = 1 Then lb1(1).Visible = True Else lb1(1).Visible = False
    If Jammed = 1 Then lb1(2).Visible = True Else lb1(2).Visible = False
    If Full = 1 Then lb1(3).Visible = True Else lb1(3).Visible = False
    If Cassette = 1 Then lb1(4).Visible = True Else lb1(4).Visible = False
    If Power = 1 Then lb2(0).Visible = True Else lb2(0).Visible = False
    If Invalid = 1 Then lb2(1).Visible = True Else lb2(1).Visible = False
    If Failure = 1 Then lb2(2).Visible = True Else lb2(2).Visible = False
End Sub

Private Sub Timer2_Timer()
    Delay = Delay + 1  'Prevent counter from double hits in case of comm. glitch
    If Delay > 50 Then Delay = 10  'prevent overflow
End Sub
