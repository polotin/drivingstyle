import cv2
import numpy as np
from moviepy.editor import VideoFileClip


def enhance_bgr_image(src, method="log"):
    if len(src.shape) < 3:
        print('Not a BGR image')
        return None
    b, g, r = cv2.split(src)
    res = None
    if method == 'log':
        en_b = np.log(1 + b.astype(np.float32))
        en_g = np.log(1 + g.astype(np.float32))
        en_r = np.log(1 + r.astype(np.float32))

        en_b = en_b / en_b.max() * 255
        en_g = en_g / en_g.max() * 255
        en_r = en_r / en_r.max() * 255

        res = cv2.merge([en_b.astype(np.uint8),
                         en_g.astype(np.uint8),
                         en_r.astype(np.uint8)])
    return res


def detect_lane_x_pos(img, turning_grad=None, turning_count=0, break_turning_count=0):
    # img = cv2.imread('D:/course/lane_detection/video/video/frame/frame37718.png')
    # img = enhance_bgr_image(img)
    dst = np.float32([[144, 233], [335, 233], [144, 250], [335, 250]])
    src = np.float32([[160, 233], [315, 233], [144, 250], [335, 250]])
    trans_mat = cv2.getPerspectiveTransform(src, dst)

    trans_img = cv2.warpPerspective(img, trans_mat, dsize=(480, 356))
    width = trans_img.shape[1]
    height = trans_img.shape[0]

    # edge = cv2.Canny(trans_img[int(0.2*height):int(0.7*height), int(0.32*width):int(0.68*width)], 50, 100)
    # trans_img = cv2.GaussianBlur(trans_img, (3, 3), 0)

    part_img = trans_img[int(0.2 * height):int(0.7 * height), int(0.2 * width):int(0.8 * width)]
    black_part_img = np.copy(part_img)
    enhance_img = np.copy(part_img)

    for i in range(part_img.shape[0]):
        for j in range(part_img.shape[1]):
            color_b = part_img[i, j, 0].astype(np.int32)
            color_g = part_img[i, j, 1].astype(np.int32)
            color_r = part_img[i, j, 2].astype(np.int32)
            color = color_b + color_g + color_r
            """
            if 20 < color_b < 100:
                enhance_img[i, j, 0] -= 20
            elif 100 < color_b < 235:
                enhance_img[i, j, 0] += 20
            if 20 < color_g < 100:
                enhance_img[i, j, 1] -= 20
            elif 100 < color_g < 235:
                enhance_img[i, j, 1] += 20
            if 20 < color_r < 100:
                enhance_img[i, j, 2] -= 20
            elif 100 < color_r < 235:
                enhance_img[i, j, 2] += 20
            """
            if color < 400 or color_b < 120 or color_g < 120 or color_r < 120:
                black_part_img[i, j, 0] = 0
                black_part_img[i, j, 1] = 0
                black_part_img[i, j, 2] = 0

    edge = cv2.Canny(black_part_img, 50, 100)
    lines = cv2.HoughLines(edge, 1, np.pi / 180, 30)
    lane1 = []
    lane2 = []
    turning_observe = []

    if lines is not None:
        for i in range(lines.shape[0]):
            rho = lines[i][0][0]
            theta = lines[i][0][1]
            a = np.cos(theta)
            b = np.sin(theta)
            x0 = a * rho
            y0 = b * rho
            x1 = int(x0 + 100 * (-b))
            y1 = int(y0 + 100 * a)
            x2 = int(x0 - 100 * (-b))
            y2 = int(y0 - 100 * a)
            if abs(x1 - x2) < 10:
                if len(lane1) == 0 or abs(0.5 * (x1 + x2) - np.mean(lane1)) < 20:
                    lane1.append(x1)
                elif len(lane2) == 0 or abs(0.5 * (x1 + x2) - np.mean(lane1)) < 20:
                    lane2.append(x2)
            elif abs(y1-y2)/abs(x1-x2) > 1:
                grad = (y1 - y2) / (x1 - x2)
                if len(turning_observe) == 0 or abs(grad - np.mean(turning_observe)) < 5:
                    turning_observe.append(grad)

            cv2.line(part_img, (x1, y1), (x2, y2), (0, 255, 0), 2)

    # cv2.imshow("img", np.vstack((part_img, black_part_img)))
    # cv2.waitKey(0)
    # cv2.imshow("edge", edge)
    # cv2.waitKey(0)

    if len(lane1) == 0 and len(lane2) == 0 and len(turning_observe) > 0:
        if turning_grad is None or abs(turning_grad - np.mean(turning_observe)) < 2:
            turning_count += 1

        # print(" grad: %f" % np.mean(turning_observe))
    else:
        if turning_count > 0:
            break_turning_count += 1
        # print("x1: %f, x2: %f" % (np.mean(lane1), np.mean(lane2)))

    return np.mean(turning_observe), turning_count, break_turning_count


def detect_lane_changing(video_path, time_pos):
    clip = VideoFileClip(video_path)
    start = time_pos - 10 if time_pos > 10 else 1
    end = time_pos + 10 if time_pos + 10 < clip.duration else clip.duration
    sub_clip = clip.subclip(start, end)

    i = 0
    _count = 0
    _grad = None
    _break_turning = 0
    _is_turning = False
    for _frame in sub_clip.iter_frames():
        """
        if int(i/900) not in acc_time:
            if count > 0:
                break_turning += 1
            i += 1
            continue
        """
        _grad, _new_count, _new_break_turning = detect_lane_x_pos(_frame, _grad, _count, _break_turning)
        # print("grad: %f, new_count: %i, new_break_turning: %i" % (grad, new_count, new_break_turning))
        if _new_break_turning > 5:
            _count = 0
            _break_turning = 0
            _grad = None
        else:
            _count = _new_count
            _break_turning = _new_break_turning
        if _count > 22 and not _is_turning:
            return True
        i += 1
    return False



if __name__ == '__main__':
    video_path = 'D:/course/lane_detection/video/video/example.mp4'
    output_path = 'D:/course/lane_detection/video/video/output_with_acc1.txt'
    acc_time_path = 'D:/course/lane_detection/video/video/acc.txt'
    clip = VideoFileClip(video_path).subclip(4170, 4180)
    out = open(output_path, 'w')
    acc_time = []
    with open(acc_time_path, 'r') as acc_time_file:
        acc_time.extend([int(i) for i in acc_time_file.read().split('\n') if len(i) > 0])

    i = 0
    count = 0
    grad = None
    break_turning = 0
    is_turning = False
    for frame in clip.iter_frames():
        """
        if int(i/900) not in acc_time:
            if count > 0:
                break_turning += 1
            i += 1
            continue
        """
        grad, new_count, new_break_turning = detect_lane_x_pos(frame, grad, count, break_turning)
        # print("grad: %f, new_count: %i, new_break_turning: %i" % (grad, new_count, new_break_turning))
        if new_break_turning > 5:
            count = 0
            break_turning = 0
            grad = None
            if is_turning:
                is_turning = False
                out.write(str(int(i / 900)) + ':' + str(int(i/15) % 60) + '\n')
                print("End Time: " + str(int(i / 900)) + ':' + str(int(i/15) % 60))
                print("-------------------A lane turning action is finished---------------------")
        else:
            count = new_count
            break_turning = new_break_turning
        if count > 22 and not is_turning:
            is_turning = True
            print("-------------------A lane turning action is detected---------------------")
            print("Start Time: " + str(int(i / 900)) + ':' + str(int(i/15) % 60))
            out.write(str(int(i / 900)) + ':' + str(int(i/15) % 60) + ',')
        i += 1

    out.close()
