<?php
//�Ը����¼�������ˮ�ж�
//����״̬�� ������Ծ�����-1.5m-1.5m֮�䣨-1.8< SMS_Y_Range_T0 <1.8��
//           ������Ծ�����7m-120m֮�䣨7< SMS_X_Range_T0 <120��
//           �Գ����ٴ���5m/s��FOT_Control_Speed > 5��
//�����¼��� ��3s���ݴ��ڣ�����״̬����10s���ϣ�countTime>100��


define("Y_Standard",1.8);               //���������Ծ���Ҫ��
define("X_Standard_Low",7);             //����������Ծ������ֵ
define("X_Standard_High",120);          //����������Ծ������ֵ
define("Speed_Standard",5);             //�����Գ����ٱ�׼
define("Time_Stardard",100);            //�����ʱ��׼
define("Tolerant_Standard",30);         //�������̶�


//$followingEvent = array();//��װ����ж��ĸ����¼��ı�ţ���ʼʱ�䣬����ʱ��
$countTime=0; //�������¼���������ʱ��
$eventId=1;
$carId=0;
$startTime=0;//��¼�¼���ʼʱ��
$eventTolerant=Tolerant_Standard;//��3s�����̶����ж����¼��Ƿ���ϸ����¼�

function carFollowing($System_Time_Stamp, $FOT_Control_Speed, $SMS_Object_ID_T0, $SMS_X_Velocity_T0, $SMS_X_Range_T0, $SMS_Y_Range_T0)
{
     global $countTime,$eventId,$carId,$startTime,$eventTolerant;//���ʺ�����ȫ�ֱ���
     global $followingEvent;                                     //���ʺ�����ȫ������


     if($SMS_Object_ID_T0!=$carId)//���״ﳵ��ID_T0���ʱ���¼�����
     {
          $carId=$SMS_Object_ID_T0;//�����ǰ����ID
          if($countTime>Time_Stardard)//��ǰһ��������״̬�ۼƴﵽ10s����
          {
               $temp=array($eventId,$startTime,$countTime);//������ʱ���飬��װ�ж���ϵ��¼���ţ���ʼʱ�䣬����ʱ��
               array_push($followingEvent,$temp);//����ʱ�������ȫ������β��
               $eventId++;                       //�¼��������
          }
          $countTime=0;                     //���ü�ʱ��
          $eventTolerant=-1;                //������̶ȣ���ʶ�¼�����

          if($SMS_Y_Range_T0>-Y_Standard && $SMS_Y_Range_T0<Y_Standard && $SMS_X_Range_T0>X_Standard_Low && $SMS_X_Range_T0<X_Standard_High && $FOT_Control_Speed>Speed_Standard)//��������¼���ϸ���״̬
          {
               $countTime++;
               $eventTolerant=Tolerant_Standard; //�������̶�
               $startTime=$System_Time_Stamp;    //�����¼���ʼʱ��
          }
     }
     else
     {
           if($SMS_Y_Range_T0>-Y_Standard && $SMS_Y_Range_T0<Y_Standard && $SMS_X_Range_T0>X_Standard_Low && $SMS_X_Range_T0<X_Standard_High && $FOT_Control_Speed>Speed_Standard)//��������¼���ϸ���״̬
           {
                $countTime++;//��ʱ������0.1s
                if($eventTolerant==-1)//��Ϊ���¼��ĵ�һ������״̬
                {
                     $startTime=$System_Time_Stamp;   //�����¼���ʼʱ��
                     $eventTolerant=Tolerant_Standard;//�������̶�
                }
                elseif($eventTolerant<Tolerant_Standard)//�����̶�С�ڳ�ʼֵ
                {
                     $countTime=$countTime+(Tolerant_Standard-$eventTolerant);
                     $eventTolerant=Tolerant_Standard;//�������̶�
                }
           }
           else
           {
                if($eventTolerant>0)//ǰһ�¼����̶ȴ���0����ǰһ�¼���δ�ж�����
                {
                     $eventTolerant--;//���̶Ƚ���
                }
                if($eventTolerant==0)//�����̶Ƚ�Ϊ0�����¼�����
                {
                     if($countTime>Time_Stardard)//���¼�����״̬�ۼƴﵽ10s����
                     {
                          $temp=array($eventId,$startTime,$countTime);//������ʱ���飬��װ�ж���ϵ��¼���ţ���ʼʱ�䣬����ʱ��
                          array_push($followingEvent,$temp);//����ʱ�������ȫ������β��
                          $eventId++;                       //�¼��������
                     }
                     $countTime=0;                     //���ü�ʱ��
                     $eventTolerant=-1;                //������̶ȣ���ʶ�¼�����
                }
           }
     }
}
