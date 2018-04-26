def calc_accuracy(predict_path, true_path):
    # 可以容忍的检测误差
    offset = 10

    pf = open(predict_path, 'r')
    tf = open(true_path, 'r')

    p_str_list = pf.read().split('\n')
    t_str_list = tf.read().split('\n')
    pf.close()
    tf.close()

    p_list = [int(p.split(':')[0])*60 + int(p.split(':')[1]) for p in p_str_list if len(p) > 0]
    t_list = [(int(t.split(',')[0].split(':')[0])*60 + int(t.split(',')[0].split(':')[1]),
               int(t.split(',')[1].split(':')[0])*60 + int(t.split(',')[1].split(':')[1])) for t in t_str_list
              if len(t) > 0]

    p_list.sort()
    num = p_list[0]
    new_p_list = [num]
    for i in range(len(p_list)):
        if abs(p_list[i] - num) > 10:
            num = p_list[i]
            new_p_list.append(num)

    hit_count = 0
    for p in new_p_list:
        start, end = 0, len(t_list) - 1
        prev_hit = hit_count
        while end > start + 1:
            mid = int((start + end) / 2)
            if t_list[mid][0] - offset <= p <= t_list[mid][1] + offset:
                hit_count += 1
                break
            elif p < t_list[mid][0] - offset:
                end = mid
            elif p > t_list[mid][1] + offset:
                start = mid
        if prev_hit == hit_count:
            print(str(int(p/60)) + ':' + str(int(p % 60)))


    print('Sensitivity: %f, Precision: %f' % (float(hit_count)/len(new_p_list), float(hit_count)/len(t_list)))


if __name__ == '__main__':
    calc_accuracy('D:/course/lane_detection/video/video/valid_lane_changing_event.txt',
                  'D:/course/lane_detection/video/video/lane_changing.txt')
