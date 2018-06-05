import sys
import csv
import os
from detection import detect_lane_changing


def read_data(file_path):
    right_side_data = []
    left_side_data = []
    offset_data = []
    road_type_data = []
    if not os.path.exists(file_path):
        return None, None
    with open(file_path, 'r', encoding='UTF-8') as f:
        reader = csv.reader(f)
        head_row = next(reader)

        # road_type_idx = head_row.index('roadType') # Check Road Type
        offset_idx = head_row.index('Road Scout.Lane_Offset')
        right_side_idx = head_row.index('Road Scout.Left_Lane_Distance_To_Right_Side')
        left_side_idx = head_row.index('Road Scout.Right_Lane_Distance_To_Left_Side')

        for row in reader:
            offset_data.append(row[offset_idx])
            right_side_data.append(row[right_side_idx])
            left_side_data.append(row[left_side_idx])
            # road_type_data.append(row[road_type_idx]) # Check Road Type
    return offset_data, road_type_data, right_side_data, left_side_data


def find_lane_changing(csv_path, video_path, with_video=False):
    od, type_d, rd, ld = read_data(csv_path)
    if od is None or rd is None or ld is None:
        with open('log.txt', 'a') as log:
            log.write("Can't open " + csv_path)
        return None

    lc_events = []
    idx = 1
    while idx < len(od):
        if len(od[idx-1]) > 0 and len(od[idx]) > 0:
            # if type_d[idx] == 'trunk' or type_d[idx] == 'motorway': # Check Road Type
            #     pass
            # else:
            #     continue

            try:
                prev = float(od[idx-1])
                curr = float(od[idx])
                if prev * curr < 0 and abs(curr - prev) > 2000:
                    lc_events.append(int(idx/10))
                    idx += 30
                else:
                    idx += 1
            except ValueError as e:
                idx += 1
                with open('log.txt', 'a') as log:
                    log.write(str(e))

    lc_events.sort()
    num = lc_events[0]
    sorted_lc = [num]
    for i in range(1, len(lc_events)):
        if abs(lc_events[i] - num) > 10:
            num = lc_events[i]
            sorted_lc.append(num)

    if with_video:
        valid_lc = []
        for t in sorted_lc:
            is_valid = detect_lane_changing(video_path, t)
            if is_valid:
                valid_lc.append(str(int(t / 60)) + ':' + str((t % 60)))
        sorted_lc = valid_lc

    res = [[i-3, i+3] for i in sorted_lc]
    for i in range(len(res)):
        if res[i][0] < 0:
            res[i][0] = 0

    return res


if __name__ == '__main__':
    video_path = sys.argv[1]
    csv_path = sys.argv[2]
    #video_path = 'video/video/example.mp4'
    #csv_path = 'video/video/CCHN_0018_229730_46_130720_0824_00228.csv'
    with_video_flag = 0
    with_video = True if with_video_flag == 1 else False
    lane_changing_list = find_lane_changing(csv_path, video_path, with_video=with_video)
    print(lane_changing_list)
