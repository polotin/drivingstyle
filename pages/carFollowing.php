<?php
//对跟车事件进行流水判定
//跟车状态： 横向相对距离在-1.5m-1.5m之间（-1.8< SMS_Y_Range_T0 <1.8）
//           纵向相对距离在7m-120m之间（7< SMS_X_Range_T0 <120）
//           自车车速大于5m/s（FOT_Control_Speed > 5）
//跟车事件： 在3s的容错内，跟车状态持续10s以上（countTime>100）


define("Y_Standard",1.8);               //定义横向相对距离要求
define("X_Standard_Low",7);             //定义纵向相对距离最低值
define("X_Standard_High",120);          //定义纵向相对距离最高值
define("Speed_Standard",5);             //定义自车车速标准
define("Time_Stardard",100);            //定义计时标准
define("Tolerant_Standard",30);         //定义容忍度


//$followingEvent = array();//承装完成判定的跟车事件的编号，起始时间，持续时间
$countTime=0; //计数该事件发生持续时间
$eventId=1;
$carId=0;
$startTime=0;//记录事件开始时间
$eventTolerant=Tolerant_Standard;//在3s的容忍度内判定该事件是否符合跟车事件

function carFollowing($System_Time_Stamp, $FOT_Control_Speed, $SMS_Object_ID_T0, $SMS_X_Velocity_T0, $SMS_X_Range_T0, $SMS_Y_Range_T0)
{
     global $countTime,$eventId,$carId,$startTime,$eventTolerant;//访问函数内全局变量
     global $followingEvent;                                     //访问函数内全局数组


     if($SMS_Object_ID_T0!=$carId)//当雷达车辆ID_T0变更时，事件结束
     {
          $carId=$SMS_Object_ID_T0;//变更当前车辆ID
          if($countTime>Time_Stardard)//若前一车辆跟车状态累计达到10s以上
          {
               $temp=array($eventId,$startTime,$countTime);//创建临时数组，承装判断完毕的事件编号，起始时间，持续时间
               array_push($followingEvent,$temp);//将临时数组加入全局数组尾部
               $eventId++;                       //事件编号自增
          }
          $countTime=0;                     //重置计时器
          $eventTolerant=-1;                //标记容忍度，标识事件结束

          if($SMS_Y_Range_T0>-Y_Standard && $SMS_Y_Range_T0<Y_Standard && $SMS_X_Range_T0>X_Standard_Low && $SMS_X_Range_T0<X_Standard_High && $FOT_Control_Speed>Speed_Standard)//若该条记录符合跟车状态
          {
               $countTime++;
               $eventTolerant=Tolerant_Standard; //重置容忍度
               $startTime=$System_Time_Stamp;    //重置事件起始时间
          }
     }
     else
     {
           if($SMS_Y_Range_T0>-Y_Standard && $SMS_Y_Range_T0<Y_Standard && $SMS_X_Range_T0>X_Standard_Low && $SMS_X_Range_T0<X_Standard_High && $FOT_Control_Speed>Speed_Standard)//若该条记录符合跟车状态
           {
                $countTime++;//计时器增加0.1s
                if($eventTolerant==-1)//若为新事件的第一条跟车状态
                {
                     $startTime=$System_Time_Stamp;   //重置事件起始时间
                     $eventTolerant=Tolerant_Standard;//重置容忍度
                }
                elseif($eventTolerant<Tolerant_Standard)//若容忍度小于初始值
                {
                     $countTime=$countTime+(Tolerant_Standard-$eventTolerant);
                     $eventTolerant=Tolerant_Standard;//重置容忍度
                }
           }
           else
           {
                if($eventTolerant>0)//前一事件容忍度大于0，则前一事件还未判定结束
                {
                     $eventTolerant--;//容忍度降低
                }
                if($eventTolerant==0)//若容忍度降为0，则事件结束
                {
                     if($countTime>Time_Stardard)//若事件跟车状态累计达到10s以上
                     {
                          $temp=array($eventId,$startTime,$countTime);//创建临时数组，承装判断完毕的事件编号，起始时间，持续时间
                          array_push($followingEvent,$temp);//将临时数组加入全局数组尾部
                          $eventId++;                       //事件编号自增
                     }
                     $countTime=0;                     //重置计时器
                     $eventTolerant=-1;                //标记容忍度，标识事件结束
                }
           }
     }
}
