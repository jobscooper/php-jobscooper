FasdUAS 1.101.10   ��   ��    k             i         I     �� 	��
�� .aevtoappnull  �   � **** 	 l      
���� 
 o      ���� 0 argv  ��  ��  ��    k            l     ��������  ��  ��        r         I     �� ���� 0 sendmail sendMail   ��  m       �   � / U s e r s / b r y a n / D r o p b o x / J o b   S e a r c h   2 0 1 3 / 2 0 1 4 _ 0 4 _ 0 5 _ n e w j o b s _ f i l t e r e d . c s v��  ��    o      ���� 0 ret        l  	 	��������  ��  ��        l  	 	��  ��    #  	if (count of argv) = 0 then     �   :   	 i f   ( c o u n t   o f   a r g v )   =   0   t h e n      l  	 	��   ��    4 . 		log "Error: No file specified for sending."      � ! ! \   	 	 l o g   " E r r o r :   N o   f i l e   s p e c i f i e d   f o r   s e n d i n g . "   " # " l  	 	�� $ %��   $   		return -1    % � & &    	 	 r e t u r n   - 1 #  ' ( ' l  	 	�� ) *��   )   	else    * � + +    	 e l s e (  , - , l  	 	�� . /��   . 0 * 		set strFileToSend to first item of argv    / � 0 0 T   	 	 s e t   s t r F i l e T o S e n d   t o   f i r s t   i t e m   o f   a r g v -  1 2 1 l  	 	�� 3 4��   3 H B 		if (characters 1 thru 1 of strFileToSend as string) = "\"" then    4 � 5 5 �   	 	 i f   ( c h a r a c t e r s   1   t h r u   1   o f   s t r F i l e T o S e n d   a s   s t r i n g )   =   " \ " "   t h e n 2  6 7 6 l  	 	�� 8 9��   8 m g 			set strFileToSend to (characters 2 thru ((length of strFileToSend) - 1)) of strFileToSend as string    9 � : : �   	 	 	 s e t   s t r F i l e T o S e n d   t o   ( c h a r a c t e r s   2   t h r u   ( ( l e n g t h   o f   s t r F i l e T o S e n d )   -   1 ) )   o f   s t r F i l e T o S e n d   a s   s t r i n g 7  ; < ; l  	 	�� = >��   =  	 		end if    > � ? ?    	 	 e n d   i f <  @ A @ l  	 	�� B C��   B H B 		log "Starting email send for CSV file = " & strFileToSend & "."    C � D D �   	 	 l o g   " S t a r t i n g   e m a i l   s e n d   f o r   C S V   f i l e   =   "   &   s t r F i l e T o S e n d   &   " . " A  E F E l  	 	�� G H��   G   	end if    H � I I    	 e n d   i f F  J K J l  	 	�� L M��   L   	    M � N N    	 K  O P O l  	 	�� Q R��   Q * $ 	set ret to sendMail(strFileToSend)    R � S S H   	 s e t   r e t   t o   s e n d M a i l ( s t r F i l e T o S e n d ) P  T U T Z   	  V W���� V l  	  X���� X A   	  Y Z Y o   	 
���� 0 ret   Z m   
 ����  ��  ��   W I   �� [��
�� .ascrcmnt****      � **** [ m     \ \ � ] ] D A n   e r r o r   o c c u r e d   s e n d i n g   t h e   f i l e .��  ��  ��   U  ^ _ ^ l   ��������  ��  ��   _  `�� ` L     a a o    ���� 0 ret  ��     b c b l     ��������  ��  ��   c  d e d i     f g f I      �� h���� 0 sendmail sendMail h  i�� i o      ���� 0 strfiletosend strFileToSend��  ��   g k    � j j  k l k l     ��������  ��  ��   l  m n m r      o p o m      q q � r r  B r y a n   S e l n e r p o      ���� 0 	recipname 	recipName n  s t s r     u v u m     w w � x x   s e l n e r @ g m a i l . c o m v o      ���� 0 recipaddress recipAddress t  y z y l   ��������  ��  ��   z  { | { r     } ~ } m    	������ ~ o      ���� 0 ret   |   �  l   ��������  ��  ��   �  � � � r     � � � I    �� ����� 80 switchtoposixpathquickhack switchToPosixPathQuickHack �  ��� � o    ���� 0 strfiletosend strFileToSend��  ��   � o      ���� 60 theattachmentfilefullpath theAttachmentFileFullPath �  � � � r     � � � I    �� ����� *0 getfilenamefrompath getFileNameFromPath �  ��� � o    ���� 60 theattachmentfilefullpath theAttachmentFileFullPath��  ��   � o      ���� .0 theattachmentfilename theAttachmentFileName �  � � � l   ��������  ��  ��   �  � � � l   ��������  ��  ��   �  � � � l   ��������  ��  ��   �  � � � l   ��������  ��  ��   �  � � � r    ! � � � m     � � � � � 
 f a l s e � o      ���� 0 
fileexists 
fileExists �  � � � Q   " ? � � � � O   % 6 � � � k   ) 5 � �  � � � r   ) 1 � � � c   ) / � � � l  ) - ����� � 4   ) -�� �
�� 
file � o   + ,���� 60 theattachmentfilefullpath theAttachmentFileFullPath��  ��   � m   - .��
�� 
alis � o      ���� 
0 myfile   �  ��� � r   2 5 � � � m   2 3 � � � � �  t r u e � o      ���� 0 
fileexists 
fileExists��   � m   % & � ��                                                                                  MACS  alis    t  Macintosh HD               ��ҨH+     >
Finder.app                                                      %Uβ�/        ����  	                CoreServices    ��C(      γ1�       >   ;   :  6Macintosh HD:System: Library: CoreServices: Finder.app   
 F i n d e r . a p p    M a c i n t o s h   H D  &System/Library/CoreServices/Finder.app  / ��   � R      �� � �
�� .ascrerr ****      � **** � o      ���� 0 errm errM � �� ���
�� 
errn � o      ���� 0 errn errN��   � l  > >��������  ��  ��   �  � � � l  @ @��������  ��  ��   �  � � � l  @ @��������  ��  ��   �  � � � Z   @ � � ��� � � =   @ C � � � o   @ A���� 0 
fileexists 
fileExists � m   A B � � � � �  t r u e � k   F c � �  � � � l  F K � � � � r   F K � � � b   F I � � � m   F G � � � � � 2 N e w   J o b   P o s t i n g s   F o u n d :     � o   G H���� .0 theattachmentfilename theAttachmentFileName � o      ���� 0 
thesubject 
theSubject �   the subject    � � � �    t h e   s u b j e c t �  � � � l  L U � � � � r   L U � � � b   L S � � � b   L Q � � � b   L O � � � m   L M � � � � � ` N e w   j o b   p o s t i n g s   f o u n d   a n d   a r e   a t t a c h e d .     F i l e :   � o   M N���� .0 theattachmentfilename theAttachmentFileName � o   O P��
�� 
ret  � o   Q R��
�� 
ret  � o      ���� 0 
thecontent 
theContent �   the content    � � � �    t h e   c o n t e n t �  � � � I  V a�� ���
�� .ascrcmnt****      � **** � b   V ] � � � b   V Y � � � m   V W � � � � �  S p e c i f i e d   � o   W X���� 0 strfiletosend strFileToSend � m   Y \ � � � � � "   C S V   f i l e   e x i s t s .��   �  ��� � l  b b��������  ��  ��  ��  ��   � k   f � � �  � � � I  f s�� ���
�� .ascrcmnt****      � **** � b   f o � � � b   f k � � � m   f i � � � � � 2 C o u l d   n o t   f i n d   s p e c i f i e d   � o   i j���� .0 theattachmentfilename theAttachmentFileName � m   k n � � � � � J   C S V   t o   s e n d .     S e n d i n g   f a i l u r e   a l e r t .��   �  � � � l  t y � � � � r   t y � � � m   t w � � � � � & F A I L E D   N E W   P O S T I N G S � o      ���� 0 
thesubject 
theSubject �   the subject    � � � �    t h e   s u b j e c t �    l  z  r   z  m   z } � F N E W   J O B   P O S T I N G S   F A I L E D   T O   D O W N L O A D o      ���� 0 
thecontent 
theContent 8 2  " & strFileName & return & return -- the content    �		 d     "   &   s t r F i l e N a m e   &   r e t u r n   &   r e t u r n   - -   t h e   c o n t e n t 

 r   � � b   � � b   � � b   � � o   � ����� 0 
thecontent 
theContent m   � � � 2 C o u l d   n o t   f i n d   s p e c i f i e d   o   � ����� .0 theattachmentfilename theAttachmentFileName m   � � �    C S V   t o   s e n d . o      �� 0 
thecontent 
theContent �~ r   � � m   � ��}�}�� o      �|�| 0 ret  �~   �  l  � ��{�z�y�{  �z  �y    s   � �  l  � �!�x�w! I  � ��v�u�t
�v .misccurdldt    ��� null�u  �t  �x  �w    o      �s�s 0 	atimedate 	aTimeDate "#" s   � �$%$ n   � �&'& 1   � ��r
�r 
shdt' l  � �(�q�p( o   � ��o�o 0 	atimedate 	aTimeDate�q  �p  % o      �n�n 0 adate aDate# )*) r   � �+,+ l  � �-�m�l- n   � �./. 1   � ��k
�k 
time/ l  � �0�j�i0 o   � ��h�h 0 	atimedate 	aTimeDate�j  �i  �m  �l  , o      �g�g 0 totalseconds totalSeconds* 121 r   � �343 _   � �565 o   � ��f�f 0 totalseconds totalSeconds6 m   � ��e�e4 o      �d�d 0 thehour theHour2 787 r   � �9:9 _   � �;<; l  � �=�c�b= `   � �>?> o   � ��a�a 0 totalseconds totalSeconds? m   � ��`�`�c  �b  < m   � ��_�_ <: o      �^�^ 0 
theminutes 
theMinutes8 @A@ r   � �BCB `   � �DED o   � ��]�] 0 totalseconds totalSecondsE m   � ��\�\ <C o      �[�[ 0 
theseconds 
theSecondsA FGF s   � �HIH b   � �JKJ b   � �LML b   � �NON b   � �PQP l  � �R�Z�YR c   � �STS o   � ��X�X 0 thehour theHourT m   � ��W
�W 
TEXT�Z  �Y  Q m   � �UU �VV  :O o   � ��V�V 0 
theminutes 
theMinutesM m   � �WW �XX  :K o   � ��U�U 0 
theseconds 
theSecondsI o      �T�T 0 atime aTimeG YZY s   � �[\[ b   � �]^] b   � �_`_ o   � ��S�S 0 adate aDate` m   � �aa �bb   ^ o   � ��R�R 0 atime aTime\ o      �Q�Q 0 
atimestamp 
aTimeStampZ cdc l  � ��P�O�N�P  �O  �N  d efe r   �ghg b   � �iji b   � �klk o   � ��M�M 0 
thesubject 
theSubjectl m   � �mm �nn   j o   � ��L�L 0 
atimestamp 
aTimeStamph o      �K�K 0 
thesubject 
theSubjectf opo r  qrq b  sts b  uvu b  wxw b  yzy o  �J�J 0 
thecontent 
theContentz o  �I
�I 
ret x o  �H
�H 
ret v m  
{{ �||  T h e   j o b   r a n   a t  t o  �G�G 0 
atimestamp 
aTimeStampr o      �F�F 0 
thecontent 
theContentp }~} l �E�D�C�E  �D  �C  ~ � l �B���B  �  	set visible to false   � ��� * 	 s e t   v i s i b l e   t o   f a l s e� ��� l ���� r  ��� m  �� ��� $ b s e l n e r @ i c l o u d . c o m� o      �A�A 0 	thesender 	theSender�   the sender   � ���    t h e   s e n d e r� ��� l �@�?�>�@  �?  �>  � ��� O  ���� k   ��� ��� r   D��� I  @�=�<�
�= .corecrel****      � null�<  � �;��
�; 
kocl� m  $'�:
�: 
bcke� �9��8
�9 
prdt� K  *:�� �7��
�7 
subj� o  -.�6�6 0 
thesubject 
theSubject� �5��
�5 
ctnt� o  12�4�4 0 
thecontent 
theContent� �3��2
�3 
pvis� m  56�1
�1 boovtrue�2  �8  � o      �0�0 
0 curmsg  � ��� l EE�/���/  �  	set sender to theSender   � ��� 0 	 s e t   s e n d e r   t o   t h e S e n d e r� ��� O  E���� k  K��� ��� r  KT��� m  KN�� ��� * b r y a n @ b r y a n s e l n e r . c o m� 1  NS�.
�. 
sndr� ��� I Uq�-�,�
�- .corecrel****      � null�,  � �+��
�+ 
kocl� m  Y\�*
�* 
trcp� �)��(
�) 
prdt� K  _k�� �'��
�' 
pnam� o  bc�&�& 0 	recipname 	recipName� �%��$
�% 
radd� o  fg�#�# 0 recipaddress recipAddress�$  �(  � ��� l rr�"�!� �"  �!  �   � ��� Z  r������ =  rw��� o  rs�� 0 
fileexists 
fileExists� m  sv�� ���  t r u e� k  z��� ��� l zz����  �  �  � ��� l zz����  � W Q make new to recipient to recipients with properties {address:"selner@gmail.com"}   � ��� �   m a k e   n e w   t o   r e c i p i e n t   t o   r e c i p i e n t s   w i t h   p r o p e r t i e s   { a d d r e s s : " s e l n e r @ g m a i l . c o m " }� ��� I z����
� .corecrel****      � null�  � ���
� 
kocl� m  ~��
� 
atts� ���
� 
prdt� K  ���� ���
� 
atfn� c  ����� o  ���� 60 theattachmentfilefullpath theAttachmentFileFullPath� m  ���
� 
alis�  �  �  �  �  �  � o  EH�� 
0 curmsg  � ��� l ����
�	�  �
  �	  � ��� I �����
� .emsgsendnull���     bcke� o  ���� 
0 curmsg  �  � ��� r  ����� m  ���� � o      �� 0 ret  �  � m  ���                                                                                  emal  alis    F  Macintosh HD               ��ҨH+     `Mail.app                                                        �iαC�        ����  	                Applications    ��C(      α�       `  #Macintosh HD:Applications: Mail.app     M a i l . a p p    M a c i n t o s h   H D  Applications/Mail.app   / ��  � ��� l ����� �  �  �   � ���� L  ���� o  ������ 0 ret  ��   e ��� l     ��������  ��  ��  � ��� i    ��� I      ������� *0 getfilenamefrompath getFileNameFromPath� ���� o      ���� 0 strfilepath strFilePath��  ��  � k     �� ��� r     ��� n     
��� 4   
���
�� 
cobj� m    	������� n    ��� I    ������� 0 
texttolist 
textToList� ��� o    ���� 0 strfilepath strFilePath� ���� m    �� ���  :��  ��  �  f     � o      ���� 0 strfilename strFileName� ��� L    �� o    ���� 0 strfilename strFileName� ���� l   ��������  ��  ��  ��  �    l     ��������  ��  ��    i     I      ������ 80 switchtoposixpathquickhack switchToPosixPathQuickHack �� o      ���� 0 strslashpath strSlashPath��  ��   k     ; 	
	 l     ����   ' ! drop a leading / if there is one    � B   d r o p   a   l e a d i n g   /   i f   t h e r e   i s   o n e
  r      o     ���� 0 strslashpath strSlashPath o      ���� 0 
strmacpath 
strMacPath  Z    -���� =     l   ���� c     n     7   ��
�� 
cha  m   	 ����  m    ����  o    ���� 0 strslashpath strSlashPath m    ��
�� 
TEXT��  ��   m     �    / r    )!"! c    '#$# n    %%&% l   %'����' 7   %��()
�� 
cha ( m    ���� ) l   $*����* \    $+,+ l   "-����- n    "./. 1     "��
�� 
leng/ o     ���� 0 strslashpath strSlashPath��  ��  , m   " #���� ��  ��  ��  ��  & o    ���� 0 strslashpath strSlashPath$ m   % &��
�� 
TEXT" o      ���� 0 
strmacpath 
strMacPath��  ��   010 l  . .��23��  2 O I Now, just swap out all the / for :.   We'll mostly be in good shape then   3 �44 �   N o w ,   j u s t   s w a p   o u t   a l l   t h e   /   f o r   : .       W e ' l l   m o s t l y   b e   i n   g o o d   s h a p e   t h e n1 565 r   . 8787 I   . 6��9���� 0 searchnreplace  9 :;: m   / 0<< �==  /; >?> m   0 1@@ �AA  :? B��B o   1 2���� 0 strslashpath strSlashPath��  ��  8 o      ���� 0 
strmacpath 
strMacPath6 C��C L   9 ;DD o   9 :���� 0 
strmacpath 
strMacPath��   EFE l     ��������  ��  ��  F GHG l     ��������  ��  ��  H IJI l     ��KL��  K 3 - I am a very old search & replace function...   L �MM Z   I   a m   a   v e r y   o l d   s e a r c h   &   r e p l a c e   f u n c t i o n . . .J NON i    PQP I      ��R���� 0 searchnreplace  R STS o      ���� 0 	searchstr  T UVU o      ���� 0 
replacestr  V W��W o      ���� 0 txt  ��  ��  Q k     :XX YZY P     7[\��[ Z    6]^����] E    _`_ o    ���� 0 txt  ` o    ���� 0 	searchstr  ^ k    2aa bcb r    ded n   fgf 1    ��
�� 
txdlg 1    ��
�� 
ascre o      ���� 0 	olddelims  c hih r    jkj J    ll m��m o    ���� 0 	searchstr  ��  k n     non 1    ��
�� 
txdlo 1    ��
�� 
ascri pqp r    rsr n    tut 2   ��
�� 
citmu o    ���� 0 txt  s o      ���� 0 txtitems  q vwv r    &xyx J    "zz {��{ o     ���� 0 
replacestr  ��  y n     |}| 1   # %��
�� 
txdl} 1   " #��
�� 
ascrw ~~ r   ' ,��� c   ' *��� o   ' (���� 0 txtitems  � m   ( )��
�� 
utxt� o      ���� 0 txt   ���� r   - 2��� o   - .���� 0 	olddelims  � n     ��� 1   / 1��
�� 
txdl� 1   . /��
�� 
ascr��  ��  ��  \ ���
�� conscase� ���
�� consdiac� ����
�� conspunc��  ��  Z ���� L   8 :�� o   8 9���� 0 txt  ��  O ��� l     ��������  ��  ��  � ��� l     ��������  ��  ��  � ��� l     ��������  ��  ��  � ��� i    ��� I      ������� 0 
texttolist 
textToList� ��� o      ���� 0 thetext theText� ���� o      ���� 0 thedelimiter theDelimiter��  ��  � k     3�� ��� r     ��� n    ��� 1    ��
�� 
txdl� 1     ��
�� 
ascr� o      ���� 0 	savedelim 	saveDelim� ��� Q    *���� k   	 �� ��� r   	 ��� J   	 �� ���� o   	 
���� 0 thedelimiter theDelimiter��  � n     ��� 1    ��
�� 
txdl� 1    ��
�� 
ascr� ��� r    ��� n    ��� 2    �~
�~ 
citm� o    �}�} 0 thetext theText� o      �|�| 0 thelist theList�  � R      �{��
�{ .ascrerr ****      � ****� o      �z�z 0 errstr errStr� �y��x
�y 
errn� o      �w�w 0 errnum errNum�x  � k    *�� ��� r    #��� o    �v�v 0 	savedelim 	saveDelim� n     ��� 1     "�u
�u 
txdl� 1     �t
�t 
ascr� ��s� R   $ *�r��
�r .ascrerr ****      � ****� o   ( )�q�q 0 errstr errStr� �p��o
�p 
errn� o   & '�n�n 0 errnum errNum�o  �s  � ��� r   + 0��� o   + ,�m�m 0 	savedelim 	saveDelim� n     ��� 1   - /�l
�l 
txdl� 1   , -�k
�k 
ascr� ��j� L   1 3�� l  1 2��i�h� o   1 2�g�g 0 thelist theList�i  �h  �j  � ��� l     �f�e�d�f  �e  �d  � ��� l     �c�b�a�c  �b  �a  � ��� l     �`�_�^�`  �_  �^  � ��]� l     �\�[�Z�\  �[  �Z  �]       �Y��������Y  � �X�W�V�U�T�S
�X .aevtoappnull  �   � ****�W 0 sendmail sendMail�V *0 getfilenamefrompath getFileNameFromPath�U 80 switchtoposixpathquickhack switchToPosixPathQuickHack�T 0 searchnreplace  �S 0 
texttolist 
textToList� �R �Q�P���O
�R .aevtoappnull  �   � ****�Q 0 argv  �P  � �N�N 0 argv  �  �M�L \�K�M 0 sendmail sendMail�L 0 ret  
�K .ascrcmnt****      � ****�O *�k+ E�O�j 
�j Y hO�� �J g�I�H���G�J 0 sendmail sendMail�I �F��F �  �E�E 0 strfiletosend strFileToSend�H  � �D�C�B�A�@�?�>�=�<�;�:�9�8�7�6�5�4�3�2�1�0�/�D 0 strfiletosend strFileToSend�C 0 	recipname 	recipName�B 0 recipaddress recipAddress�A 0 ret  �@ 60 theattachmentfilefullpath theAttachmentFileFullPath�? .0 theattachmentfilename theAttachmentFileName�> 0 
fileexists 
fileExists�= 
0 myfile  �< 0 errm errM�; 0 errn errN�: 0 
thesubject 
theSubject�9 0 
thecontent 
theContent�8 0 	atimedate 	aTimeDate�7 0 adate aDate�6 0 totalseconds totalSeconds�5 0 thehour theHour�4 0 
theminutes 
theMinutes�3 0 
theseconds 
theSeconds�2 0 atime aTime�1 0 
atimestamp 
aTimeStamp�0 0 	thesender 	theSender�/ 
0 curmsg  � 7 q w�.�- � ��,�+ ��*� � � ��) � ��( � � ��'�&�%�$�#�"UWam{���!� �����������������. 80 switchtoposixpathquickhack switchToPosixPathQuickHack�- *0 getfilenamefrompath getFileNameFromPath
�, 
file
�+ 
alis�* 0 errm errM� ���
� 
errn� 0 errn errN�  
�) 
ret 
�( .ascrcmnt****      � ****
�' .misccurdldt    ��� null
�& 
shdt
�% 
time�$�# <
�" 
TEXT
�! 
kocl
�  
bcke
� 
prdt
� 
subj
� 
ctnt
� 
pvis� � 
� .corecrel****      � null
� 
sndr
� 
trcp
� 
pnam
� 
radd
� 
atts
� 
atfn
� .emsgsendnull���     bcke�G��E�O�E�OiE�O*�k+ E�O*�k+ E�O�E�O � *�/�&E�O�E�UW X 	 
hO��  "�%E�O��%�%�%E�O�%a %j OPY -a �%a %j Oa E�Oa E�O�a %�%a %E�OiE�O*j EQ�O�a ,EQ�O�a ,E�O�a "E�O�a #a "E^ O�a #E^ O�a &a %] %a %] %EQ^ O�a  %] %EQ^ O�a !%] %E�O��%�%a "%] %E�Oa #E^ Oa $ �*a %a &a 'a (�a )�a *ea +a , -E^ O]  Ma .*a /,FO*a %a 0a 'a 1�a 2�a ,a , -O�a 3  *a %a 4a 'a 5��&la , -Y hUO] j 6OkE�UO�� �������� *0 getfilenamefrompath getFileNameFromPath� �
��
 �  �	�	 0 strfilepath strFilePath�  � ��� 0 strfilepath strFilePath� 0 strfilename strFileName� ���� 0 
texttolist 
textToList
� 
cobj� )��l+ �i/E�O�OP� ������� 80 switchtoposixpathquickhack switchToPosixPathQuickHack� � ��  �  ���� 0 strslashpath strSlashPath�  � ������ 0 strslashpath strSlashPath�� 0 
strmacpath 
strMacPath� ������<@��
�� 
cha 
�� 
TEXT
�� 
leng�� 0 searchnreplace  � <�E�O�[�\[Zk\Zk2�&�  �[�\[Zl\Z��,k2�&E�Y hO*��m+ E�O�� ��Q���������� 0 searchnreplace  �� ����� �  �������� 0 	searchstr  �� 0 
replacestr  �� 0 txt  ��  � ������������ 0 	searchstr  �� 0 
replacestr  �� 0 txt  �� 0 	olddelims  �� 0 txtitems  � \��������
�� 
ascr
�� 
txdl
�� 
citm
�� 
utxt�� ;�g 4�� ,��,E�O�kv��,FO��-E�O�kv��,FO��&E�O���,FY hVO�� ������������� 0 
texttolist 
textToList�� ����� �  ������ 0 thetext theText�� 0 thedelimiter theDelimiter��  � �������������� 0 thetext theText�� 0 thedelimiter theDelimiter�� 0 	savedelim 	saveDelim�� 0 thelist theList�� 0 errstr errStr�� 0 errnum errNum� �����������
�� 
ascr
�� 
txdl
�� 
citm�� 0 errstr errStr� ������
�� 
errn�� 0 errnum errNum��  
�� 
errn�� 4��,E�O �kv��,FO��-E�W X  ���,FO)�l�O���,FO�ascr  ��ޭ