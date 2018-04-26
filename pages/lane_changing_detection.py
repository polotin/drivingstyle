import sys
import csv
import os
from detection import detect_lane_changing


def read_data(file_path):
    right_side_data = []
    left_side_data = []
    if not os.path.exists(file_path):
        return None, None
    with open(file_path, 'r', encoding='UTF-8') as f:
        reader = csv.reader(f)
        head_row = next(reader)

        right_side_idx = head_row.index('Road Scout.Left_Lane_Distance_To_Right_Side')
        left_side_idx = head_row.index('Road Scout.Right_Lane_Distance_To_Left_Side')

        for row in reader:
            right_side_data.append(row[right_side_idx])
            left_side_data.append(row[left_side_idx])
    return right_side_data, left_side_data


def find_lane_changing(csv_path, video_path, with_video=False):
    rd, ld = read_data(csv_path)
    if rd is None or ld is None:
        with open('log.txt', 'a') as log:
            log.write("Can't open " + csv_path)
        return None

    lc_events = []
    for i in range(1, len(ld)):
        if len(ld[i-1]) > 0 and len(ld[i]) > 0:
            try:
                prev = float(ld[i - 1])
                curr = float(ld[i])
                if prev * curr < 0 and abs(curr) < 1500 and abs(curr - prev) < 200:
                    lc_events.append(int(i/10))
            except ValueError as e:
                with open('log.txt', 'a') as log:
                    log.write(str(e))
    lc_events.sort()
    num = lc_events[0]
    sorted_lc = [num]
    for i in range(1, len(lc_events)):
        if abs(lc_events[i] - num) > 10:
            num = lc_events[i]
            sorted_lc.append(num)

    valid_lc = []
    if with_video:
        for t in sorted_lc:
            is_valid = detect_lane_changing(video_path, t)
            if is_valid:
                valid_lc.append(str(int(t / 60)) + ':' + str((t % 60)))
    else:
        valid_lc = sorted_lc

    return valid_lc

if __name__ == '__main__':
    #video_path = sys.argv[1]
    #csv_path = sys.argv[2]
    #with_video_flag = sys.argv[3]
    video_path = '../video/video/CCHN_0018_229730_46_130720_0824_00228_Front.mp4'
    csv_path = 'data/input_folder/CCHN_0018_229730_46_130720_0824_00228.csv'
    with_video_flag = 1
    with_video = True if with_video_flag == 1 else False
    lane_changing_list = find_lane_changing(csv_path, video_path, with_video)
    print(lane_changing_list)
