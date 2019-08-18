# export a sine wave indexed by time range 24 hours

# combine 2 waves
t <- seq(0, pi * 2, length.out = 1441) # 0 to 1 to -1 to 0
y <- round((sin(t) + sin(t * 2)*1.2 ), 2)
# y <- round((sin(t) + sin(t * 2) + sin(t*3)/2), 2) # unnecessary

# limit range to 0 to 1
y[y>1] <- 1
y[y<=0.1] <- 0.1
# preview
plot(t,y,type="l" )

library(lubridate)
dt_start <- floor_date(now(), unit="day")
dt_index <- seq.POSIXt(from = dt_start, to = (dt_start + days(1)), by="min")
#plot (dt_index, y, type ='l' )

out <- data.frame( dt = dt_index, val = y)
write.csv(out, file="out.csv")
