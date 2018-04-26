import xlrd
from detection import detect_lane_changing


def find_lane_changing(xlsx_path, out_path):
    workbook = xlrd.open_workbook(xlsx_path)
    sheet = workbook.sheet_by_index(0)
    out = open(out_path, 'w')

    table_names = sheet.row_values(0)[:]
    right_side_pos = table_names.index('Road Scout.Left_Lane_Distance_To_Right_Side')
    left_side_pos = table_names.index('Road Scout.Right_Lane_Distance_To_Left_Side')
    right_side_data = sheet.col_values(right_side_pos)[:]
    left_side_data = sheet.col_values(left_side_pos)[:]
    # right_side_data = [(sheet.col_values(0)[i], sheet.col_values(right_side_pos)[i]) for i in range(1, sheet.nrows)
    #                    if isinstance(sheet.col_values(right_side_pos)[i], float)]
    # left_side_data = [sheet.col_values(left_side_pos)[i] for i in range(1, sheet.ncols)
    #                   if isinstance(sheet.col_values(left_side_pos)[i], float)]

    for i in range(1, len(right_side_data)):
        if isinstance(right_side_data[i-1], float) and isinstance(right_side_data[i], float):
            if right_side_data[i] * right_side_data[i-1] < 0 and abs(right_side_data[i]) < 1500\
                    and abs(right_side_data[i] - right_side_data[i-1]) < 200:

                out.write(str(int(i/600)) + ':' + str((int(i/10)) % 60) + '\n')
                print(str(int(i/600)) + ':' + str((int(i/10)) % 60))
    for i in range(1, len(left_side_data)):
        if isinstance(left_side_data[i-1], float) and isinstance(left_side_data[i], float):
            if left_side_data[i] * left_side_data[i-1] < 0 and abs(left_side_data[i]) < 1500 \
                    and abs(left_side_data[i] - left_side_data[i - 1]) < 200:
                out.write(str(int(i/600)) + ':' + str((int(i/10)) % 60) + '\n')
                print(str(int(i/600)) + ':' + str((int(i/10)) % 60))
    out.close()


def validate_lane_changing(lane_changing_file_path, video_file_path):
    lcf = open(lane_changing_file_path, 'r')
    time_list = lcf.read().split('\n')
    time_list = [int(t.split(':')[0])*60 + int(t.split(':')[1]) for t in time_list if len(t) > 0]
    lcf.close()

    valid_time_list = []
    for t in time_list:
        is_valid = detect_lane_changing(video_file_path, t)
        if is_valid:
            valid_time_list.append(str(int(t/60)) + ':' + str((t % 60)))

    valid_time_list.sort()
    num = valid_time_list[0]
    new_valid_list = [num]
    for i in range(1, len(valid_time_list)):
        if abs(valid_time_list[i] - num) > 10:
            num = valid_time_list[i]
            new_valid_list.append(num)
    return new_valid_list


if __name__ == '__main__':
    #find_lane_changing('video/video/CCHN_0018_229730_46_130720_0824_00228.xlsx',
    #                   'video/video/lane_changing_event1.txt')
    vtl = validate_lane_changing('video/video/lane_changing_event1.txt',
                                 'D:/course/lane_detection/video/video/example.mp4')
    with open('video/video/valid_lane_changing_event.txt', 'w') as out:
        for v in vtl:
            out.write(v + '\n')
