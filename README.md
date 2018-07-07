# drivingstyle
# 无用文件已经删除

目录结构
Config.json   --配置信息存储

css文件夹：
  index.css   --首页样式文件
  process-bar.css   --转动的进度条样式
  
js文件夹：
  analyse.js    --分析结果的表格展示逻辑的实现，以及表格中按钮的点击事件绑定
  index.js    --首页逻辑的实现
  login.js    --登录页面逻辑实现
  
pages文件夹下是各个页面：
  index.php   --首页，打开首页会检查session，查看登录状态，未登录或session过期则跳转至登录界面
  validate_login.php    --检查登录状态
  login.php   --登录
  getDriverList.php   --在首页输入数据文件目录后扫描目录，获取到驾驶员编号列表
  config.php    --配置页面
  configure.php   --配置类
  process_improve.php   --在首页提交表单后，此文件调用process_handler.php中的分析方法，并且负责导出新文件和生成json串
  process_handler.php   --根据数据文件对勾选的事件进行分析判断
  event_handler.php   --供process_handler.php调用，对急刹车和急转弯事件进行判断
  carFollowing.php    --判断跟车事件
  lane_change_detection.php   --调用python脚本，识别变道事件
  detection.py/ read_xlsx.py/ measure.py/ lane_changing_detection.py
      --变道事件识别代码，识别结果以List的形式写入目录下的lane_change_list文件中
  trip.php    --行程类
  event.php   --事件类
  event_chart.php   --事件数据图表类
  eventChart.php    --事件中各数据图绘制页面
  video_play.php    --视频播放页面
