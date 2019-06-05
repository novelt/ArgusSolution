# Send mails libraries

import smtplib
# Import the email modules we'll need
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

import config
import globals

def getMessage(mail_subject, mail_from, mail_to, mail_body):
	msg = MIMEMultipart()
	msg['Subject'] = mail_subject
	msg['From'] = mail_from
	msg['To'] = globals.COMMA_SEPARATOR.join(mail_to)
		
	content = MIMEText(mail_body, 'html')
	msg.attach(content)
	
	print 'Msg'
	print msg.as_string()
	
	return msg

def sendMail(mail_subject, mail_from, mail_to, mail_body):
	
	message = getMessage(mail_subject, mail_from, mail_to, mail_body)
	print 'Try Send'
	
	if config.smtp_SSL == 1:
		sendMail_SSL(message, mail_to)
	elif config.smtp_TLS == 1:
		sendMail_TLS(message, mail_to)
	else:
		sendMail_(message, mail_to)

		
def sendMail_TLS(msg, mail_to):
	s = smtplib.SMTP(config.smtp_host, config.smtp_port)
	# identify ourselves to smtp gmail client
	s.ehlo()
	# secure our email with tls encryption
	s.starttls()
	# re-identify ourselves as an encrypted connection
	s.ehlo()
	s.login(config.smtp_login, config.smtp_password)	
	s.sendmail(msg['From'], mail_to, msg.as_string())
	s.quit()

def sendMail_SSL(msg, mail_to):
	s = smtplib.SMTP_SSL(config.smtp_host, config.smtp_port)	
	# identify ourselves to smtp gmail client
	s.ehlo()
	s.login(config.smtp_login, config.smtp_password)	
	s.sendmail(msg['From'], mail_to, msg.as_string())
	s.quit()

def sendMail_(msg, mail_to):		
	s = smtplib.SMTP(config.smtp_host, config.smtp_port)
	if config.smtp_login != '': 
		s.login(config.smtp_login, config.smtp_password)	
	s.sendmail(msg['From'], mail_to, msg.as_string())
	s.quit()
