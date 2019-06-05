#!/usr/bin/env python
# -*- coding: utf-8 -*-
# *********************************
# 		Monitoring Scheduler
# *********************************
 
from Tkinter import * 
import tkMessageBox
import webbrowser
import winsound
import time
from datetime import datetime
import urllib2
import config
import globals
import mails

class ArgusMonitoringGui:
	def __init__(self):
		self.__mainWindow = Tk()
		
		#compteur
		self.compteur = 0
		
		#to check if alarm is running or not 
		self.alarmEnabled = True 
		self.alarmRunning = False
				
		self.__mainWindow.title("ARGUS - Scheduling and monitoring - " + config.version)
		self.__mainWindow.wm_iconbitmap(config.icon_default)
		
		self.__schedulingFrame = LabelFrame(self.__mainWindow, text="Scheduling", padx=10, pady=10)
		self.__schedulingFrame.grid(row=0, column=0, columnspan=3, padx=5, pady=5, sticky=N+S+E+W)
		
		self.__nextRun = Label(self.__schedulingFrame, text="Wait for start")
		self.__nextRun.pack()
		self.__lastRun = Label(self.__schedulingFrame, text="Wait for start")
		self.__lastRun.pack()
		self.__lblStatusInterval = Label(self.__schedulingFrame, text="Interval of " + str(config.interval) + " seconds between runs")
		self.__lblStatusInterval.pack()
		
		self.__testResultsFrame = LabelFrame(self.__mainWindow, text="Tests results", padx=10, pady=10)
		self.__testResultsFrame.grid(row=1, column=0, columnspan=3, padx=5, pady=5, sticky=N+S+E+W)
		
		self.__testWeb = Label(self.__testResultsFrame, text="Wait for start")
		self.__testWeb.pack()
		
		self.__testApplicatif = Label(self.__testResultsFrame, text="Wait for start")
		self.__testApplicatif.pack()
		
		self.__btnShowDiagnosis = Button(self.__mainWindow, text="Show detailed diagnosis", font="-size 10 -weight bold", command=self.openDiagnosis)
		self.__btnShowDiagnosis.grid(row=2, column=0, rowspan=3, padx=5, pady=5, sticky=N+S+E+W)
		
		self.__btnForceSchedule = Button(self.__mainWindow, text="Force schedule", command=self.forceSchedule)
		self.__btnForceSchedule.grid(row=2, column=1, padx=1, pady=5, sticky=N+S+E+W)
		
		self.__btnAlarm = Button(self.__mainWindow, text="Alarm test", command=self.alarm)
		self.__btnAlarm.grid(row=3, column=1, padx=1, pady=5, sticky=N+S+E+W)
		
		self.__btnTestMail = Button(self.__mainWindow, text="Test Mail", command=self.testMail)
		self.__btnTestMail.grid(row=4, column=1, padx=1, pady=5, sticky=N+S+E+W)

		self.__btnHideWindow = Button(self.__mainWindow, text="Hide this windows", font="-size 10 -weight bold", command=self.hide)
		self.__btnHideWindow.grid(row=2, column=2, rowspan=3, padx=5, pady=5, sticky=N+S+E+W)
		
		self.__mainWindow.protocol("WM_DELETE_WINDOW", self.on_closing)
			
	def hide(self):
		self.__mainWindow.iconify()
	
	def show(self):
		self.__mainWindow.deiconify()
			
	def testMail(self):
	
		if (config.smtp_Enabled == False):
			tkMessageBox.showinfo('SMTP configuration disabled', 'The SMTP functionality is disabled in the settings')
			return 
	
		if (not config.mail_test_subject ):
			tkMessageBox.showerror('Please specify a mail test subject', 'Please specifiy the variable mail_test_subject in the config.py file')
			return
		
		if (not config.mail_test_from ):
			tkMessageBox.showerror('Please specify a mail test from', 'Please specifiy the variable mail_test_from in the config.py file')
			return
		
		if (not config.mail_test_to):
			tkMessageBox.showerror('Please specify a mail test to', 'Please specifiy the variable mail_test_to in the config.py file')
			return
		
		if (not config.mail_test_body ):
			tkMessageBox.showerror('Please specify a mail test body', 'Please specifiy the variable mail_test_body in the config.py file')
			return
	
		try :
			mails.sendMail(config.mail_test_subject, config.mail_test_from, config.mail_test_to, config.mail_test_body)
			tkMessageBox.showinfo('Mail successfully sent', 'The configuration of SMTP settings is OK')
			print 'Send Test Mail successfully'
			
		except Exception, ex :
			tkMessageBox.showerror('Mail not sent, please verify the SMTP settings', ex)
			print 'Send Test Mail error'
			print ex
	
	def forceSchedule(self):
		TestHttp(self)
	
	def openDiagnosis(self):
		webbrowser.open(config.url_diagnosis_page)
			
	def alarm(self):
		if (self.alarmRunning == True):
			self.alarmEnabled = False
			self.alarmRunning = False
			self.alarmDeActiv()
		else:
			PlayAlarm()
			
	def setNextRun(self):
		self.compteur = self.compteur + 1
		self.__nextRun["text"] = "Next run in " + str(config.interval - self.compteur) + " seconds"
		
	def setLastRun(self, time):
		self.compteur = 0
		self.__lastRun["text"] = "Last run : " + time
	
	def setTestWebError(self, error):
		self.__testWeb["text"] = error
		self.__testWeb["font"] = "-size 10 -weight bold"
		self.__testWeb["fg"] = "red"
	
	def setTestWebSuccess(self):
		self.__testWeb["text"] = "The local webserver is running"
		self.__testWeb["font"] = "-size 10 -weight bold"
		self.__testWeb["fg"] = "#32CD32"
		
	def setTestApplicatifSuccess(self):
		self.__testApplicatif["text"] = "All application diagnosis are good"
		self.__testApplicatif["font"] = "-size 10 -weight bold"
		self.__testApplicatif["fg"] = "#32CD32"
	
	def setTestApplicatifError(self):
		self.__testApplicatif["text"] = "Application ERROR: Check the detailed diagnosis!"
		self.__testApplicatif["font"] = "-size 10 -weight bold"
		self.__testApplicatif["fg"] = "red"
	
	def displayIcon(self, icon):
		self.__mainWindow.wm_iconbitmap(icon)
	
	def alarmActiv(self):
		self.__btnAlarm["text"] = "Stop Alarm"
		self.__btnAlarm["fg"] = "red"
	
	def alarmDeActiv(self):
		self.__btnAlarm["text"] = "Alarm test"
		self.__btnAlarm["fg"] = "black"
	
	def destroy(self):
		self.__mainWindow.destroy()
	
	def on_closing(self):
		if tkMessageBox.askokcancel("Quit", "Do you want to stop the scheduler ? If closed, no more tests or tasks will be run"):
			self.destroy()	
	
	def LoopInterval(self):
		self.__mainWindow.after(1000, lambda: CheckInterval(self))
	
	def LoopAlarm(self):
		PlayAlarm()
		self.__mainWindow.after(5000, lambda: Alarm(self))

def PlayAlarm():
	winsound.PlaySound(config.wav_alarm, winsound.SND_FILENAME | winsound.SND_ASYNC | winsound.SND_NOWAIT )
		
def Alarm(myGui):
	if (config.play_alarm):
		if (myGui.alarmEnabled == True):
			myGui.alarmActiv()
			myGui.alarmRunning = True
			myGui.LoopAlarm()
		else:
			myGui.alarmRunning == False
		
def CheckInterval(myGui):
	try:
		if myGui.compteur >= config.interval :
			TestHttp(myGui)

		myGui.setNextRun() 
		myGui.LoopInterval()
	except Exception, ex :
		print 'CheckInterval error'
		print ex

def SendMail():

	if (config.smtp_Enabled == False):
		print 'SendMail function disabled'
		return 

	# convert to unix timestamp
	now = datetime.now()
	timeDiff = now - globals.LAST_SEND_MAIL_TIME
	minutes = timeDiff.total_seconds()/60
	# they are now in seconds, subtract and then divide by 60 to get minutes.
	print minutes
	
	if minutes > config.mail_interval :
		print 'SendMail function'
		
		try :
			mails.sendMail(config.mail_subject, config.mail_from, config.mail_to, config.mail_body)
			print 'Send Mail successfully'
			globals.LAST_SEND_MAIL_TIME = datetime.now()
			
		except Exception, ex :
			print 'Send Mail error'
			print ex

def TestHttp(myGui) :
	testWeb = False 
	testApp = False
	error = ''
	try:
		content =  urllib2.urlopen(config.url_surveillance_page).read()
		testWeb = True

		if config.diagnosis_ok not in content:
			testApp = False
		else:
			testApp = True
			
	except Exception, ex :
		testWeb = False
		error = ex
		print ex
	
	if testWeb:
		print 'testWeb Success'
		myGui.setTestWebSuccess()
	else:
		print 'testWeb Error'
		myGui.setTestWebError(error)
	
	if testApp:
		print 'testApp Success'
		myGui.setTestApplicatifSuccess()
	else:
		print 'testApp Error'
		myGui.setTestApplicatifError()
	
	if not testApp or not testWeb:
		myGui.displayIcon(config.icon_error)
		SendMail()
		Alarm(myGui)
		myGui.show()
		
	# Update Last send Mail Time to an old date if everything is ok to be able to send mail immediately if everything is going wrong
	if (testApp and testWeb) :
		myGui.displayIcon(config.icon_success)
		globals.LAST_SEND_MAIL_TIME = datetime(1970, 1, 1, 00, 00, 01)
	
	print 'setLastRun'
	myGui.setLastRun(time.ctime())

	
myGUI = ArgusMonitoringGui()
CheckInterval(myGUI)	
mainloop()




