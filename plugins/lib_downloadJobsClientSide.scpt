FasdUAS 1.101.10   ��   ��    k             j     �� �� ,0 strjsgetmaxpagevalue strJSGetMaxPageValue  m        � 	 	     
  
 j    �� �� *0 strgetnextpagevalue strGetNextPageValue  m       �          j    �� �� ,0 strjsclicknext_first strJSClickNext_First  m       �          j   	 �� �� .0 strjsclicknext_others strJSClickNext_Others  m   	 
   �          j    �� �� &0 strjsgetthesource strJSGetTheSource  m       �          j    ��  �� &0 nindexmaxforclick nIndexMaxForClick   m    ����    ! " ! j    �� #�� 0 stroutputdir strOutputDir # m     $ $ � % %   "  & ' & j    �� (�� 0 strsitename strSiteName ( m     ) ) � * *   '  + , + j    �� -�� 0 
strfilekey 
strFileKey - m     . . � / /   ,  0 1 0 j    �� 2�� 0 strurl strURL 2 m     3 3 � 4 4   1  5 6 5 j     $�� 7�� 0 strreturnhtml strReturnHTML 7 m     # 8 8 � 9 9   6  : ; : l     ��������  ��  ��   ;  < = < i   % ( > ? > I      �� @���� 0 test   @  A�� A o      ���� 0 argv  ��  ��   ? k     R B B  C D C l     ��������  ��  ��   D  E F E r      G H G m      I I � J J n / U s e r s / b r y a n / C o d e / d a t a / j o b s _ d e b u g / 2 0 1 4 - 0 6 - 1 6 _ 1 5 2 3 _ j o b s / H o      ���� 0 stroutputdir strOutputDir F  K L K r     M N M m    	 O O � P P  A m a z o n N o      ���� 0 strsitename strSiteName L  Q R Q r     S T S m     U U � V V   a m a z o n - t e s t - j o b s T o      ���� 0 
strfilekey 
strFileKey R  W X W r     Y Z Y m     [ [ � \ \ � h t t p : / / w w w . a m a z o n . j o b s / r e s u l t s ? s j i d = 6 8 , 8 3 & c h e c k l i d = @ % 2 7 U S , % 2 0 W A , % 2 0 S e a t t l e % 2 7 & c n a m e = % 2 7 U S , % 2 0 W A , % 2 0 S e a t t l e % 2 7 Z o      ���� 0 strurl strURL X  ] ^ ] l     ��������  ��  ��   ^  _ ` _ r     ' a b a m     ! c c � d d� f u n c t i o n   g e t M a x P a g e V a l u e ( )   {   v a r   s t r I t e m   =     d o c u m e n t . g e t E l e m e n t B y I d ( ' s e a r c h P r o f i l e s ' ) . f i r s t C h i l d . n e x t S i b l i n g . n e x t S i b l i n g . n e x t S i b l i n g . f i r s t C h i l d . n e x t S i b l i n g . t e x t C o n t e n t ;   r e t u r n   s t r I t e m . s p l i t ( '   ' ) [ 2 ] ;     }     g e t M a x P a g e V a l u e ( ) ; b o      ���� ,0 strjsgetmaxpagevalue strJSGetMaxPageValue `  e f e l  ( (��������  ��  ��   f  g h g r   ( / i j i m   ( ) k k � l l � f u n c t i o n   g e t N e x t P a g e V a l u e ( )   { r e t u r n   d o c u m e n t . g e t E l e m e n t B y I d ( ' n e x t p a g e ' ) . v a l u e ; }   g e t N e x t P a g e V a l u e ( ) ; j o      ���� *0 strgetnextpagevalue strGetNextPageValue h  m n m l  0 0��������  ��  ��   n  o p o r   0 7 q r q m   0 1 s s � t tV f u n c t i o n   d o G e t J o b s C l i c k ( $ n I n d e x )   {   i f ( d o c u m e n t . g e t E l e m e n t s B y C l a s s N a m e ( ' p a g e   g r a d i e n t ' ) [ 0 ]   = =   n u l l )   r e t u r n   f a l s e ;   v a r   e v e n t   =   d o c u m e n t . c r e a t e E v e n t ( ' M o u s e E v e n t s ' ) ;               e v e n t . i n i t M o u s e E v e n t ( ' c l i c k ' ,   t r u e ,   t r u e ,   w i n d o w ,                 0 ,   0 ,   0 ,   0 ,   0 ,     
 	 	                         f a l s e ,   f a l s e ,   f a l s e ,   f a l s e ,   
 	 	                         0 ,   n u l l ) ;   
 	 	                 d o c u m e n t . g e t E l e m e n t s B y C l a s s N a m e ( ' p a g e   g r a d i e n t ' ) [ 0 ] . d i s p a t c h E v e n t ( e v e n t ) ;   r e t u r n   t r u e ;   }     d o G e t J o b s C l i c k ( ) ; r o      ���� ,0 strjsclicknext_first strJSClickNext_First p  u v u l  8 8��������  ��  ��   v  w x w r   8 ? y z y m   8 9 { { � | |F f u n c t i o n   d o G e t J o b s C l i c k ( )   {   i f ( d o c u m e n t . g e t E l e m e n t s B y C l a s s N a m e ( ' p a g e   g r a d i e n t ' ) [ 1 ]   = =   n u l l )   r e t u r n   f a l s e ;   v a r   e v e n t   =   d o c u m e n t . c r e a t e E v e n t ( ' M o u s e E v e n t s ' ) ;               e v e n t . i n i t M o u s e E v e n t ( ' c l i c k ' ,   t r u e ,   t r u e ,   w i n d o w ,                 0 ,   0 ,   0 ,   0 ,   0 ,     
 	 	                         f a l s e ,   f a l s e ,   f a l s e ,   f a l s e ,   
 	 	                         0 ,   n u l l ) ;   
 	 	                 d o c u m e n t . g e t E l e m e n t s B y C l a s s N a m e ( ' p a g e   g r a d i e n t ' ) [ 1 ] . d i s p a t c h E v e n t ( e v e n t ) ;   r e t u r n   t r u e ;   }   d o G e t J o b s C l i c k ( ) ; z o      ���� .0 strjsclicknext_others strJSClickNext_Others x  } ~ } l  @ @��������  ��  ��   ~   �  r   @ G � � � m   @ A � � � � � � f u n c t i o n   g e t H T M L ( )   {   r e t u r n   d o c u m e n t . g e t E l e m e n t B y I d ( ' t e a m j o b s ' ) . i n n e r H T M L ;   }   g e t H T M L ( ) ; � o      ���� &0 strjsgetthesource strJSGetTheSource �  � � � l  H H��������  ��  ��   �  � � � r   H O � � � I   H M��������  0 dojobsdownload doJobsDownload��  ��   � o      ���� 0 ret   �  ��� � L   P R � � o   P Q���� 0 ret  ��   =  � � � l     ��������  ��  ��   �  � � � l     ��������  ��  ��   �  � � � i   ) , � � � I      ��������  0 dojobsdownload doJobsDownload��  ��   � k     � �  � � � l     ��������  ��  ��   �  � � � r      � � � m     ������ � o      ���� 0 ret   �  � � � l   ��������  ��  ��   �  � � � I   �� ���
�� .ascrcmnt****      � **** � b     � � � b     � � � b     � � � m     � � � � � D S t a r t i n g   d o w n l o a d   o f   t h e   H T M L   f o r   � o    
���� 0 
strfilekey 
strFileKey � m     � � � � �    s e a r c h :   � o    ���� 0 strurl strURL��   �  � � � I   #�� ���
�� .ascrcmnt****      � **** � b     � � � m     � � � � � 4 O u t p u t   w i l l   b e   w r i t t e n   t o   � o    ���� 0 stroutputdir strOutputDir��   �  � � � r   $ 0 � � � I   $ .�� ����� 20 getorcreateoutputfolder getOrCreateOutputFolder �  ��� � o   % *���� 0 stroutputdir strOutputDir��  ��   � o      ���� 0 	outfolder 	outFolder �  � � � l  1 1��������  ��  ��   �  � � � r   1 : � � � b   1 8 � � � o   1 6���� 0 
strfilekey 
strFileKey � m   6 7 � � � � �  - j o b s - p a g e - � o      ���� 0 thefilebase theFileBase �  � � � l  ; ;��������  ��  ��   �  � � � Q   ; � � � � k   >� � �  � � � l  > >��������  ��  ��   �  � � � O  > H � � � I  B G������
�� .aevtquitnull��� ��� null��  ��   � m   > ? � ��                                                                                  sfri  alis    N  Macintosh HD               ��ҨH+     `
Safari.app                                                       !W͜,r        ����  	                Applications    ��C(      ͜��       `  %Macintosh HD:Applications: Safari.app    
 S a f a r i . a p p    M a c i n t o s h   H D  Applications/Safari.app   / ��   �  � � � I  I N�� ���
�� .sysodelanull��� ��� nmbr � m   I J���� ��   �  � � � l  O O��������  ��  ��   �  � � � O   O� � � � k   S� � �  � � � I  S X������
�� .miscactvnull��� ��� null��  ��   �  � � � r   Y _ � � � 4   Y ]�� �
�� 
cwin � m   [ \����  � o      ���� 0 	curwindow 	curWindow �  � � � r   ` f � � � n   ` d � � � 4   a d�� �
�� 
bTab � m   b c����  � o   ` a���� 0 	curwindow 	curWindow � o      ���� 0 curtab curTab �  � � � r   g p � � � o   g l���� 0 strurl strURL � n       � � � 1   m o��
�� 
pURL � o   l m���� 0 curtab curTab �  � � � l  q q��������  ��  ��   �  � � � I  q v�� ���
�� .sysodelanull��� ��� nmbr � m   q r���� ��   �  � � � l  w w��������  ��  ��   �  � � � Z   w � � ��� � � l  w ~ ���~ � >  w ~ � � � o   w |�}�} ,0 strjsgetmaxpagevalue strJSGetMaxPageValue � m   | } � � � � �  �  �~   � k   � �    r   � � I  � ��|
�| .sfridojs****       utxt o   � ��{�{ ,0 strjsgetmaxpagevalue strJSGetMaxPageValue �z�y
�z 
dcnm 4   � ��x
�x 
docu m   � ��w�w �y   o      �v�v 0 strmaxpages strMaxPages 	�u	 r   � �

 c   � � l  � ��t�s I  � ��r
�r .sfridojs****       utxt o   � ��q�q ,0 strjsgetmaxpagevalue strJSGetMaxPageValue �p�o
�p 
dcnm 4   � ��n
�n 
docu m   � ��m�m �o  �t  �s   m   � ��l
�l 
long o      �k�k 0 	nmaxpages 	nMaxPages�u  ��   � k   � �  r   � � m   � ��j�j  o      �i�i 0 strmaxpages strMaxPages  r   � � m   � ��h�h  o      �g�g 0 	nmaxpages 	nMaxPages �f l  � ��e�d�c�e  �d  �c  �f   �  l  � ��b�a�`�b  �a  �`     l  � ��_�^�]�_  �^  �]    !"! I  � ��\#�[
�\ .ascrcmnt****      � ****# b   � �$%$ b   � �&'& m   � �(( �))   S e a r c h   r e t u r n e d  ' o   � ��Z�Z 0 	nmaxpages 	nMaxPages% m   � �** �++    p a g e s   o f   j o b s .�[  " ,-, l  � ��Y�X�W�Y  �X  �W  - ./. Z   � �01�V20 l  � �3�U�T3 >  � �454 o   � ��S�S *0 strgetnextpagevalue strGetNextPageValue5 m   � �66 �77  �U  �T  1 r   � �898 c   � �:;: l  � �<�R�Q< I  � ��P=>
�P .sfridojs****       utxt= o   � ��O�O *0 strgetnextpagevalue strGetNextPageValue> �N?�M
�N 
dcnm? 4   � ��L@
�L 
docu@ m   � ��K�K �M  �R  �Q  ; m   � ��J
�J 
long9 o      �I�I 0 strnextpage strNextPage�V  2 r   � �ABA m   � ��H�H B o      �G�G 0 strnextpage strNextPage/ CDC l  � ��F�E�D�F  �E  �D  D EFE l  � ��C�B�A�C  �B  �A  F GHG r   � �IJI m   � ��@
�@ boovtrueJ o      �?�? "0 boolclickednext boolClickedNextH KLK r   � �MNM o   � ��>�> ,0 strjsclicknext_first strJSClickNext_FirstN o      �=�=  0 strjsclicknext strJSClickNextL OPO r   �QRQ l  � �S�<�;S c   � �TUT o   � ��:�: 0 strnextpage strNextPageU m   � ��9
�9 
long�<  �;  R o      �8�8 0 	nnextpage 	nNextPageP VWV l �7�6�5�7  �6  �5  W XYX V  �Z[Z k  �\\ ]^] l �4�3�2�4  �3  �2  ^ _`_ r  *aba c  (cdc l $e�1�0e I $�/fg
�/ .sfridojs****       utxtf o  �.�. *0 strgetnextpagevalue strGetNextPageValueg �-h�,
�- 
dcnmh 4   �+i
�+ 
docui m  �*�* �,  �1  �0  d m  $'�)
�) 
longb o      �(�( 0 	nnextpage 	nNextPage` jkj l ++�'�&�%�'  �&  �%  k lml r  +=non I +;�$pq
�$ .sfridojs****       utxtp o  +0�#�# &0 strjsgetthesource strJSGetTheSourceq �"r�!
�" 
dcnmr 4  17� s
�  
docus m  56�� �!  o o      �� 0 	thesource 	theSourcem tut l >>����  �  �  u vwv r  >exyx b  >_z{z b  >]|}| b  >Y~~ b  >U��� b  >Q��� b  >O��� b  >K��� b  >G��� o  >C�� 0 strreturnhtml strReturnHTML� o  CF�
� 
ret � o  GJ�
� 
ret � m  KN�� ���  < ! - -   p a g e  � o  OP�� 0 	nnextpage 	nNextPage� m  QT�� ��� 
   - - - > o  UX�
� 
ret } o  Y\�
� 
ret { o  ]^�� 0 	thesource 	theSourcey o      �� 0 strreturnhtml strReturnHTMLw ��� I fm���
� .ascrcmnt****      � ****� m  fi�� ��� r C l i c k i n g   N e x t   b u t t o n   t o   m o v e   t o   t h e   n e x t   r e s u l t s   p a g e . . . .�  � ��� l nn����  �  �  � ��� l nn����  �  �  � ��� Z  n����
�� l ns��	�� > ns��� o  no��  0 strjsclicknext strJSClickNext� m  or�� ���  �	  �  � k  v��� ��� r  v���� I v����
� .sfridojs****       utxt� o  vw��  0 strjsclicknext strJSClickNext� ���
� 
dcnm� 4  x~��
� 
docu� m  |}�� �  � o      � �  "0 boolclickednext boolClickedNext� ���� r  ����� o  ������ .0 strjsclicknext_others strJSClickNext_Others� o      ����  0 strjsclicknext strJSClickNext��  �
  � r  ����� m  ����
�� boovfals� o      ���� "0 boolclickednext boolClickedNext� ��� l ����������  ��  ��  � ��� I �������
�� .sysodelanull��� ��� nmbr� m  ������ ��  � ���� l ����������  ��  ��  ��  [ l ������ F  ��� A  	��� o  ���� 0 	nnextpage 	nNextPage� o  ���� 0 	nmaxpages 	nMaxPages� =  ��� o  ���� "0 boolclickednext boolClickedNext� m  ��
�� boovtrue��  ��  Y ��� l ����������  ��  ��  � ��� I �������
�� .ascrcmnt****      � ****� b  ����� m  ���� ��� * C u r r e n t   N e x t   P a g e   i s  � o  ������ 0 	nnextpage 	nNextPage��  � ��� l ����������  ��  ��  � ���� I ��������
�� .aevtquitnull��� ��� null��  ��  ��   � m   O P���                                                                                  sfri  alis    N  Macintosh HD               ��ҨH+     `
Safari.app                                                       !W͜,r        ����  	                Applications    ��C(      ͜��       `  %Macintosh HD:Applications: Safari.app    
 S a f a r i . a p p    M a c i n t o s h   H D  Applications/Safari.app   / ��   � ��� l ����������  ��  ��  � ��� r  ����� c  ����� l �������� b  ����� b  ����� o  ������ 0 	outfolder 	outFolder� o  ������ 0 thefilebase theFileBase� m  ���� ��� 
 . h t m l��  ��  � m  ����
�� 
TEXT� o      ���� "0 thefullfilepath theFullFilePath� ��� I �������
�� .ascrcmnt****      � ****� b  ����� b  ����� b  ����� m  ���� ��� @ S a v i n g   t h e   s o u r c e   H T M L   f o r   p a g e  � o  ������ 0 	nnextpage 	nNextPage� m  ���� ���    t o  � o  ������ "0 thefullfilepath theFullFilePath��  � ��� I  ��������� 0 writetofile writeToFile� ��� o  ������ 0 strreturnhtml strReturnHTML� ���� o  ������ "0 thefullfilepath theFullFilePath��  ��  � ��� l ����������  ��  ��  � ��� I �������
�� .ascrcmnt****      � ****� b  ����� b  ����� m  ���� ��� @ C o m p l e t e d   d o w n l o a d i n g   H T M L   f o r    � o  ������ 0 
strfilekey 
strFileKey� m  ���� ���      j o b s .��  � ��� l ������ L  ���� m  ������ �  	 success    � ���    s u c c e s s  � ���� l ����������  ��  ��  ��   � R      �� 
�� .ascrerr ****      � ****  o      ���� 0 errstr errStr ����
�� 
errn o      ���� 0 errnum errNum��   � k  �  I �����
�� .ascrcmnt****      � **** b  � b  �	
	 b  � m  �� � 8 F a i l e d   t o   d o w n l o a d   j o b s   f o r   o  ����� 0 
strfilekey 
strFileKey
 m   �  .     E r r o r :     o  ���� 0 errstr errStr��    L   m  ������ �� l ��������  ��  ��  ��   � �� l ��������  ��  ��  ��   �  l     ��������  ��  ��    l     ��������  ��  ��    i   - 0 I      ������ 20 getorcreateoutputfolder getOrCreateOutputFolder �� o      ���� "0 stroutputfolder strOutputFolder��  ��   k     �   !"! l     ��������  ��  ��  " #$# r     %&% l    '����' 4     ��(
�� 
psxf( o    ���� "0 stroutputfolder strOutputFolder��  ��  & o      ���� $0 pathoutputfolder pathOutputFolder$ )*) l   ��������  ��  ��  * +,+ l   ��������  ��  ��  , -.- I   ��/��
�� .ascrcmnt****      � ****/ b    010 b    
232 m    44 �55 6 S e t t i n g   u p   o u t p u t   f o l d e r :   '3 o    	���� $0 pathoutputfolder pathOutputFolder1 m   
 66 �77  '��  . 898 l   ��������  ��  ��  9 :;: r    <=< m    ��
�� boovfals= o      ���� 0 
fldrexists 
fldrExists; >?> Q    0@A��@ O    'BCB r    &DED I   $��F��
�� .coredoexbool        obj F 4     ��G
�� 
cfolG o    ���� $0 pathoutputfolder pathOutputFolder��  E o      ���� 0 
fldrexists 
fldrExistsC m    HH�                                                                                  MACS  alis    t  Macintosh HD               ��ҨH+     >
Finder.app                                                      %Uβ�/        ����  	                CoreServices    ��C(      γ1�       >   ;   :  6Macintosh HD:System: Library: CoreServices: Finder.app   
 F i n d e r . a p p    M a c i n t o s h   H D  &System/Library/CoreServices/Finder.app  / ��  A R      ������
�� .ascrerr ****      � ****��  ��  ��  ? IJI l  1 1��������  ��  ��  J KLK Z   1 �MN��OM H   1 3PP o   1 2���� 0 
fldrexists 
fldrExistsN k   6 �QQ RSR r   6 BTUT n   6 @VWV 4  = @��X
�� 
cobjX m   > ?����W n  6 =YZY I   7 =�~[�}�~ 0 
texttolist 
textToList[ \]\ o   7 8�|�| $0 pathoutputfolder pathOutputFolder] ^�{^ m   8 9__ �``  :�{  �}  Z  f   6 7U o      �z�z $0 nameoutputfolder nameOutputFolderS aba r   C Lcdc l  C Je�y�xe \   C Jfgf l  C Fh�w�vh n   C Fiji 1   D F�u
�u 
lengj o   C D�t�t $0 pathoutputfolder pathOutputFolder�w  �v  g l  F Ik�s�rk n   F Ilml 1   G I�q
�q 
lengm o   F G�p�p $0 nameoutputfolder nameOutputFolder�s  �r  �y  �x  d o      �o�o $0 lengthparentpath lengthParentPathb non I  M T�np�m
�n .ascrcmnt****      � ****p b   M Pqrq m   M Nss �tt  p a r e n t   =  r o   N O�l�l $0 lengthparentpath lengthParentPath�m  o uvu r   U dwxw c   U byzy n   U `{|{ 7  V `�k}~
�k 
ctxt} m   Z \�j�j ~ o   ] _�i�i $0 lengthparentpath lengthParentPath| o   U V�h�h $0 pathoutputfolder pathOutputFolderz m   ` a�g
�g 
TEXTx o      �f�f 00 pathoutputfolderparent pathOutputFolderParentv � O   e ���� k   i ��� ��� l  i i�e�d�c�e  �d  �c  � ��� I  i |�b��a
�b .ascrcmnt****      � ****� b   i x��� b   i t��� b   i r��� b   i n��� m   i l�� ��� 0 C r e a t i n g   o u t p u t   f o l d e r   '� o   l m�`�` $0 nameoutputfolder nameOutputFolder� m   n q�� ���  '   a t   '� o   r s�_�_ $0 pathoutputfolder pathOutputFolder� m   t w�� ���  ' .�a  � ��� r   } ���� I  } ��^�]�
�^ .corecrel****      � null�]  � �\��
�\ 
kocl� m   � ��[
�[ 
cfol� �Z��
�Z 
insh� 4   � ��Y�
�Y 
cfol� o   � ��X�X 00 pathoutputfolderparent pathOutputFolderParent� �W��V
�W 
prdt� K   � ��� �U��T
�U 
pnam� o   � ��S�S $0 nameoutputfolder nameOutputFolder�T  �V  � o      �R�R 0 	returnval 	returnVal� ��Q� l  � ��P�O�N�P  �O  �N  �Q  � m   e f���                                                                                  MACS  alis    t  Macintosh HD               ��ҨH+     >
Finder.app                                                      %Uβ�/        ����  	                CoreServices    ��C(      γ1�       >   ;   :  6Macintosh HD:System: Library: CoreServices: Finder.app   
 F i n d e r . a p p    M a c i n t o s h   H D  &System/Library/CoreServices/Finder.app  / ��  � ��� l  � ��M�L�K�M  �L  �K  � ��� I  � ��J��I
�J .ascrcmnt****      � ****� m   � ��� ��� T O u t p u t   f o l d e r   d o e s   n o t   y e t   e x i s t ;   c r e a t e d .�I  � ��H� l  � ��G�F�E�G  �F  �E  �H  ��  O k   � ��� ��� O   � ���� r   � ���� l  � ���D�C� c   � ���� o   � ��B�B $0 pathoutputfolder pathOutputFolder� m   � ��A
�A 
alis�D  �C  � o      �@�@ 0 	returnval 	returnVal� m   � ����                                                                                  MACS  alis    t  Macintosh HD               ��ҨH+     >
Finder.app                                                      %Uβ�/        ����  	                CoreServices    ��C(      γ1�       >   ;   :  6Macintosh HD:System: Library: CoreServices: Finder.app   
 F i n d e r . a p p    M a c i n t o s h   H D  &System/Library/CoreServices/Finder.app  / ��  � ��?� l  � ��>�=�<�>  �=  �<  �?  L ��� l  � ��;�:�9�;  �:  �9  � ��� L   � ��� o   � ��8�8 0 	returnval 	returnVal� ��7� l  � ��6�5�4�6  �5  �4  �7   ��� l     �3�2�1�3  �2  �1  � ��� l     �0�/�.�0  �/  �.  � ��� l     �-���-  � 3 - I am a very old search & replace function...   � ��� Z   I   a m   a   v e r y   o l d   s e a r c h   &   r e p l a c e   f u n c t i o n . . .� ��� i   1 4��� I      �,��+�, 0 searchnreplace  � ��� o      �*�* 0 	searchstr  � ��� o      �)�) 0 
replacestr  � ��(� o      �'�' 0 txt  �(  �+  � k     :�� ��� P     7���&� Z    6���%�$� E    ��� o    �#�# 0 txt  � o    �"�" 0 	searchstr  � k    2�� ��� r    ��� n   ��� 1    �!
�! 
txdl� 1    � 
�  
ascr� o      �� 0 	olddelims  � ��� r    ��� J    �� ��� o    �� 0 	searchstr  �  � n     ��� 1    �
� 
txdl� 1    �
� 
ascr� ��� r    ��� n    ��� 2   �
� 
citm� o    �� 0 txt  � o      �� 0 txtitems  � ��� r    &��� J    "�� ��� o     �� 0 
replacestr  �  � n     ��� 1   # %�
� 
txdl� 1   " #�
� 
ascr� ��� r   ' ,��� c   ' *��� o   ' (�� 0 txtitems  � m   ( )�
� 
utxt� o      �� 0 txt  � ��� r   - 2   o   - .�� 0 	olddelims   n      1   / 1�
� 
txdl 1   . /�
� 
ascr�  �%  �$  � �
� conscase �
� consdiac �
�	
�
 conspunc�	  �&  � � L   8 : o   8 9�� 0 txt  �  � 	 l     ����  �  �  	 

 l     ����  �  �    i   5 8 I      � ���  0 writetofile writeToFile  o      ���� 0 totalstring TotalString �� o      ���� 0 strfilepath strFilePath��  ��   k       r      I    
��
�� .rdwropenshor       file 4     ��
�� 
file o    ���� 0 strfilepath strFilePath ����
�� 
perm m    ��
�� boovtrue��   o      ���� $0 thefilereference theFileReference  I   �� 
�� .rdwrwritnull���     **** o    ���� 0 totalstring TotalString  ��!��
�� 
refn! o    ���� $0 thefilereference theFileReference��   "��" I   ��#��
�� .rdwrclosnull���     ****# o    ���� $0 thefilereference theFileReference��  ��   $%$ l     ��������  ��  ��  % &'& i   9 <()( I      ��*���� $0 writetofile_orig writeToFile_Orig* +,+ o      ���� 0 strhtml strHTML, -��- o      ���� 0 strfile strFile��  ��  ) k     i.. /0/ l     ��������  ��  ��  0 121 O     343 Z    56����5 I   	��7��
�� .coredoexbool        obj 7 o    ���� "0 thefullfilepath theFullFilePath��  6 I   ��8��
�� .coredeloobj        obj 8 o    ���� "0 thefullfilepath theFullFilePath��  ��  ��  4 m     99�                                                                                  MACS  alis    t  Macintosh HD               ��ҨH+     >
Finder.app                                                      %Uβ�/        ����  	                CoreServices    ��C(      γ1�       >   ;   :  6Macintosh HD:System: Library: CoreServices: Finder.app   
 F i n d e r . a p p    M a c i n t o s h   H D  &System/Library/CoreServices/Finder.app  / ��  2 :;: l   ��������  ��  ��  ; <=< r    >?> o    ���� "0 thefullfilepath theFullFilePath? o      ���� 0 thefile theFile= @A@ Q    gBCDB k    :EE FGF r    *HIH I   (��JK
�� .rdwropenshor       fileJ 4    "��L
�� 
fileL o     !���� 0 thefile theFileK ��M��
�� 
permM m   # $��
�� boovtrue��  I o      ���� 0 fref fRefG NON I  + 4��PQ
�� .rdwrwritnull���     ****P o   + ,���� 0 	thesource 	theSourceQ ��RS
�� 
refnR o   - .���� 0 fref fRefS ��T��
�� 
as  T m   / 0��
�� 
utf8��  O U��U I  5 :��V��
�� .rdwrclosnull���     ****V o   5 6���� 0 fref fRef��  ��  C R      ������
�� .ascrerr ****      � ****��  ��  D k   B gWW XYX Q   B VZ[��Z I  E M��\��
�� .rdwrclosnull���     ****\ 4   E I��]
�� 
file] o   G H���� 0 thefile theFile��  [ R      ������
�� .ascrerr ****      � ****��  ��  ��  Y ^_^ I  W d��`��
�� .ascrcmnt****      � ****` b   W `aba b   W ^cdc m   W Xee �ff ( A n   e r r o r   o c c u r e d .      d o   X ]���� 0 
strfilekey 
strFileKeyb m   ^ _gg �hh @   j o b s   d o w n l o a d   d i d   n o t   c o m p l e t e .��  _ i��i L   e gjj m   e f��������  A k��k l  h h��������  ��  ��  ��  ' lml l     ��������  ��  ��  m non l     ��������  ��  ��  o p��p i   = @qrq I      ��s���� 0 
texttolist 
textToLists tut o      ���� 0 thetext theTextu v��v o      ���� 0 thedelimiter theDelimiter��  ��  r k     3ww xyx r     z{z n    |}| 1    ��
�� 
txdl} 1     ��
�� 
ascr{ o      ���� 0 	savedelim 	saveDelimy ~~ Q    *���� k   	 �� ��� r   	 ��� J   	 �� ���� o   	 
���� 0 thedelimiter theDelimiter��  � n     ��� 1    ��
�� 
txdl� 1    ��
�� 
ascr� ���� r    ��� n    ��� 2    ��
�� 
citm� o    ���� 0 thetext theText� o      ���� 0 thelist theList��  � R      ����
�� .ascrerr ****      � ****� o      ���� 0 errstr errStr� �����
�� 
errn� o      ���� 0 errnum errNum��  � k    *�� ��� r    #��� o    ���� 0 	savedelim 	saveDelim� n     ��� 1     "��
�� 
txdl� 1     ��
�� 
ascr� ���� R   $ *����
�� .ascrerr ****      � ****� o   ( )���� 0 errstr errStr� �����
�� 
errn� o   & '���� 0 errnum errNum��  ��   ��� r   + 0��� o   + ,���� 0 	savedelim 	saveDelim� n     ��� 1   - /��
�� 
txdl� 1   , -��
�� 
ascr� ���� L   1 3�� l  1 2������ o   1 2���� 0 thelist theList��  ��  ��  ��       ���     �� $ ) . 3 8���������  � ��~�}�|�{�z�y�x�w�v�u�t�s�r�q�p�o�n� ,0 strjsgetmaxpagevalue strJSGetMaxPageValue�~ *0 strgetnextpagevalue strGetNextPageValue�} ,0 strjsclicknext_first strJSClickNext_First�| .0 strjsclicknext_others strJSClickNext_Others�{ &0 strjsgetthesource strJSGetTheSource�z &0 nindexmaxforclick nIndexMaxForClick�y 0 stroutputdir strOutputDir�x 0 strsitename strSiteName�w 0 
strfilekey 
strFileKey�v 0 strurl strURL�u 0 strreturnhtml strReturnHTML�t 0 test  �s  0 dojobsdownload doJobsDownload�r 20 getorcreateoutputfolder getOrCreateOutputFolder�q 0 searchnreplace  �p 0 writetofile writeToFile�o $0 writetofile_orig writeToFile_Orig�n 0 
texttolist 
textToList�� � �m ?�l�k���j�m 0 test  �l �i��i �  �h�h 0 argv  �k  � �g�f�g 0 argv  �f 0 ret  � 
 I O U [ c k s { ��e�e  0 dojobsdownload doJobsDownload�j S�Ec  O�Ec  O�Ec  O�Ec  	O�Ec   O�Ec  O�Ec  O�Ec  O�Ec  O*j+ 	E�O�� �d ��c�b���a�d  0 dojobsdownload doJobsDownload�c  �b  � �`�_�^�]�\�[�Z�Y�X�W�V�U�T�S�R�` 0 ret  �_ 0 	outfolder 	outFolder�^ 0 thefilebase theFileBase�] 0 	curwindow 	curWindow�\ 0 curtab curTab�[ 0 strmaxpages strMaxPages�Z 0 	nmaxpages 	nMaxPages�Y 0 strnextpage strNextPage�X "0 boolclickednext boolClickedNext�W  0 strjsclicknext strJSClickNext�V 0 	nnextpage 	nNextPage�U 0 	thesource 	theSource�T "0 thefullfilepath theFullFilePath�S 0 errstr errStr�R 0 errnum errNum� ( � ��Q ��P � ��O�N�M�L�K�J�I ��H�G�F�E(*6�D�C�������B���A���@�
�Q .ascrcmnt****      � ****�P 20 getorcreateoutputfolder getOrCreateOutputFolder
�O .aevtquitnull��� ��� null
�N .sysodelanull��� ��� nmbr
�M .miscactvnull��� ��� null
�L 
cwin
�K 
bTab
�J 
pURL�I 
�H 
dcnm
�G 
docu
�F .sfridojs****       utxt
�E 
long
�D 
bool
�C 
ret 
�B 
TEXT�A 0 writetofile writeToFile�@ 0 errstr errStr� �?�>�=
�? 
errn�> 0 errnum errNum�=  �aiE�O�b  %�%b  	%j O�b  %j O*b  k+ E�Ob  �%E�O�� *j UOlj O�^*j 	O*�k/E�O��k/E�Ob  	��,FO�j Ob   � .b   �*a k/l E�Ob   �*a k/l a &E�Y kE�OkE�OPOa �%a %j Ob  a  b  �*a k/l a &E�Y kE�OeE�Ob  E�O�a &E�O �h��	 	�e a &b  �*a k/l a &E�Ob  �*a k/l E�Ob  
_ %_ %a %�%a %_ %_ %�%Ec  
Oa j O�a  ��*a k/l E�Ob  E�Y fE�O�j OP[OY�iOa �%j O*j UO��%a %a &E�Oa �%a  %�%j O*b  
�l+ !Oa "b  %a #%j OkOPW X $ %a &b  %a '%�%j OiOPOP� �<�;�:���9�< 20 getorcreateoutputfolder getOrCreateOutputFolder�; �8��8 �  �7�7 "0 stroutputfolder strOutputFolder�:  � �6�5�4�3�2�1�0�6 "0 stroutputfolder strOutputFolder�5 $0 pathoutputfolder pathOutputFolder�4 0 
fldrexists 
fldrExists�3 $0 nameoutputfolder nameOutputFolder�2 $0 lengthparentpath lengthParentPath�1 00 pathoutputfolderparent pathOutputFolderParent�0 0 	returnval 	returnVal� �/46�.H�-�,�+�*_�)�(�'s�&�%����$�#�"�!� ���
�/ 
psxf
�. .ascrcmnt****      � ****
�- 
cfol
�, .coredoexbool        obj �+  �*  �) 0 
texttolist 
textToList
�( 
cobj
�' 
leng
�& 
ctxt
�% 
TEXT
�$ 
kocl
�# 
insh
�" 
prdt
�! 
pnam�  
� .corecrel****      � null
� 
alis�9 �*�/E�O�%�%j OfE�O � *�/j E�UW X  hO� v)��l+ 
�i/E�O��,��,E�O��%j O�[�\[Zk\Z�2�&E�O� 5a �%a %�%a %j O*a �a *�/a a �la  E�OPUOa j OPY � 	�a &E�UOPO�OP� �������� 0 searchnreplace  � ��� �  ���� 0 	searchstr  � 0 
replacestr  � 0 txt  �  � ������ 0 	searchstr  � 0 
replacestr  � 0 txt  � 0 	olddelims  � 0 txtitems  � �����
� 
ascr
� 
txdl
� 
citm
� 
utxt� ;�g 4�� ,��,E�O�kv��,FO��-E�O�kv��,FO��&E�O���,FY hVO�� ���
���	� 0 writetofile writeToFile� ��� �  ��� 0 totalstring TotalString� 0 strfilepath strFilePath�
  � ���� 0 totalstring TotalString� 0 strfilepath strFilePath� $0 thefilereference theFileReference� ��� ������
� 
file
� 
perm
�  .rdwropenshor       file
�� 
refn
�� .rdwrwritnull���     ****
�� .rdwrclosnull���     ****�	 *�/�el E�O��l O�j � ��)���������� $0 writetofile_orig writeToFile_Orig�� ����� �  ������ 0 strhtml strHTML�� 0 strfile strFile��  � �������������� 0 strhtml strHTML�� 0 strfile strFile�� "0 thefullfilepath theFullFilePath�� 0 thefile theFile�� 0 fref fRef�� 0 	thesource 	theSource� 9��������������������������eg��
�� .coredoexbool        obj 
�� .coredeloobj        obj 
�� 
file
�� 
perm
�� .rdwropenshor       file
�� 
refn
�� 
as  
�� 
utf8�� 
�� .rdwrwritnull���     ****
�� .rdwrclosnull���     ****��  ��  
�� .ascrcmnt****      � ****�� j� �j  
�j Y hUO�E�O !*�/�el E�O����� 
O�j W ,X   *�/j W X  hO�b  %�%j OiOP� ��r���������� 0 
texttolist 
textToList�� ����� �  ������ 0 thetext theText�� 0 thedelimiter theDelimiter��  � �������������� 0 thetext theText�� 0 thedelimiter theDelimiter�� 0 	savedelim 	saveDelim�� 0 thelist theList�� 0 errstr errStr�� 0 errnum errNum� �����������
�� 
ascr
�� 
txdl
�� 
citm�� 0 errstr errStr� ������
�� 
errn�� 0 errnum errNum��  
�� 
errn�� 4��,E�O �kv��,FO��-E�W X  ���,FO)�l�O���,FO�ascr  ��ޭ