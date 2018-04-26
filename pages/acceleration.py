import xlrd

workbook = xlrd.open_workbook('D:/course/lane_detection/video/video/CCHN_0018_229730_46_130720_0824_00228.xlsx')
sheet = workbook.sheet_by_index(0)

out = open('D:/course/lane_detection/video/video/acc.txt', 'w')

for row in range(1, sheet.nrows):
    time_stamp = float(sheet.cell(row, 0).value)
    acc_y = sheet.cell(row, 4).value
    if not isinstance(acc_y, float):
        continue
    if acc_y > 0.05:
        print('Time: %f, Acceleration: %f' % (time_stamp, acc_y))
        out.write(str(int(time_stamp / 10)) + '\n')

out.close()
