#!/usr/bin/env python

from time import sleep
import numpy, sys, csv, datetime, random, traceback
from influxdb import InfluxDBClient

# base_line value from 0 to 1, val_factor to multiply to integer val_factor = 2000
dist_scale=100
base_line_scale=1000
# val normal dist SD
sd = 0.2
# pause control, in ms
pause_low=1
pause_high=10
# throttle limits write speed
writer_pause_cnt = 1000
writer_pause_sec = 5
# per min average values over 24 hours
base_values={}
# local docker
inf_con = None

######################################################################
# read baseline value, indexed by time as string in one minute interval
# csv file is created by line_24h.R script
# 1, has to be exactly 1441 elements, 24*60
# 2, value from 0 to 1, float
# this suppose to be average traffic over 24 hours, but that scrpt 
# just use sine summary, not real PROD data, for now
######################################################################
def imp_rate():
    with open('line1.out') as csvfile:
        reader = csv.reader(csvfile)
        for row in reader:
            if row[1] != 'dt':
               base_values[row[1][11:]]=float(row[2])

#for tm, val in base_values.items():
#    print(tm,val)

######################################################################
# connect to database
######################################################################
def connect_db():
    try:
        con = InfluxDBClient('localhost', 8086,'','','techx')
        return con
    except:
        print("cant connect to influxdb")

######################################################################
# create value base on time index
# normal dist, scaled
# more explaination later
# v2 note: return value is per minute rate
######################################################################
def get_rate(tm_index):

    try:
        value = round(numpy.random.normal(base_values[tm_index] * base_line_scale,  dist_scale ))
        if value < 0:
            value = 0
    except KeyError:
        # since index range from 0 to 1, random pick a number between 0 and base line scale
        value = round(numpy.random.normal( random.randint(0, base_line_scale) ,  dist_scale ))

    return int(value)


######################################################################
# write output, (date time) value
# date time format: yyyy/mm/ddThh:mm:ssZ
# should write bigger json instead of insert single data point,
# if I have time
# skip write if value is 0
######################################################################
def write_val(dt,val):
#    print(dt.strftime("%Y-%m-%dT%H:%M:%SZ, ")+str(val))
#    sys.stdout.flush()
    if val <= 0:
        return True
    json_body = [ {
        "measurement": "sim_val",
        "time": dt.strftime("%Y-%m-%dT%H:%M:%SZ"),
        "fields": { "val" : val }
        } ]
    inf_con.write_points(json_body)


######################################################################
# current time value, pause before write
# v2 note: pause first, then use pause to divide rate 
# think of how long since last value havent write, cummulate paused seconds
######################################################################
def write_current():
    
    pause=random.randint(pause_low,pause_high)
    sleep(pause)

    # TZ TBD
    Now=datetime.datetime.utcnow()
    # today as format yyyy/mm/dd
    dt=Now.strftime("%x")
    # current minute
    tm=Now.strftime("%X")[0:5]+':00'
    
    write_val(Now, int(get_rate(tm) *  pause / 60 ))

######################################################################
# no sleep, advance by paused second from start time to end time
# to control traffic to output(influxdb), use pause by count
######################################################################
def write_range(from_time, to_time):
    cnt=0
    while from_time <= to_time:
        tm=from_time.strftime("%X")[0:5]+':00'
        pause=random.randint(pause_low,pause_high)
        write_val(from_time, int(get_rate(tm) * pause / 60 ))
        from_time = from_time + datetime.timedelta(seconds=pause)

        cnt = cnt + 1
        if cnt % writer_pause_cnt == 0:
            sleep(writer_pause_sec)
            print("%d wrote, last timestamp: %s" % (cnt, from_time))
            sys.stdout.flush()

######################################################################
# write a range need time, once it finished, you cant call write 
# current directly, it will leave a gap 
######################################################################
def write_until_now(from_time):
    
    now = datetime.datetime.utcnow()
    # loop condition:
    # from_time is less than a mean(pause high and low) before now
    while from_time < now - datetime.timedelta(seconds = (pause_high - pause_low)/2 + pause_low ):
        write_range(from_time, now)    
        from_time =  now
        now = datetime.datetime.utcnow()


######################################################################
# there wont be error if not data is deleted
######################################################################
def purge_range(from_time, to_time):
    try:
        query_str='delete from "sim_val" where time >= \''+from_time.strftime("%Y-%m-%dT%H:%M:%SZ")+'\' and time < \''+to_time.strftime("%Y-%m-%dT%H:%M:%SZ"+'\'')
        inf_con.query(query_str)
        return True
    except:
        print("error delete data")
        print(query_str)

######################################################################
# find last data point timestamp, return datetime
######################################################################
def last_tm():
    try:
        query_str='select last("val") from sim_val'
        res = inf_con.query(query_str)
        for p in res.get_points():
            last_timestamp = p['time']
        return datetime.datetime.strptime(last_timestamp, '%Y-%m-%dT%H:%M:%SZ')
    except:
        print("cant query last data point timestamp")
        print(query_str)


try:
    imp_rate()
    inf_con = connect_db()

# test one insert
#    write_current()

# test backfill
# UTC
#    time_start = datetime.datetime.strptime('2019-08-10T00:00:00','%Y-%m-%dT%H:%M:%S')
#    time_end = datetime.datetime.strptime('2019-08-18T00:00:00','%Y-%m-%dT%H:%M:%S')
#    purge_range(time_start,time_end)
#    write_range(time_start,time_end)

# catch up until now
    last_dt = last_tm()
    write_until_now(last_dt)

# real time
    while True:
       write_current()
except:
    print("error....")
    print(sys.exc_info()[0])
    print(traceback.format_exc())

