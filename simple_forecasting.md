# simple forecasting
## average, mean of ts val
`meanf(ts, h)`, ts input time series, h forecast horizon.
## Naive, last ovservation
`naive(ts,h)`
## seasonal naive
`snaive(ts,h)`, last seasonal observation
```
beer2 <- window(ausbeer,start=1992,end=c(2007,4))
autoplot(beer2) +  autolayer(snaive(beer2, h=11)
 ```
 ## Drife
 `rwf(y,h, drift=T)`, line of first and last observation
#transformation
## box-cox
```
lambda <- BoxCox.lambda(elec)
autoplot(BoxCox(elec,lambda))
```
## bias-adjusted
back-transform if box-cox transformation is used

# residuals, judge if model is adequate
A good forecasting method will yield residuals with the following properties:
- The residuals are uncorrelated. If there are correlations between residuals, then there is information left in the residuals which should be used in computing forecasts.
- The residuals have zero mean. If the residuals have a mean other than zero, then the forecasts are biased.
- The residuals have constant variance.
- The residuals are normally distributed.
## residual acf (autocorrelation) 
`acf(ts, lag.max,plot=T/F)` can be used to see if ts is autocorrelated or not, high autocorreate has value on on one side, and 
normally closing to zero as lag increase, low correcllated ts tend to be on both side(up and down)
`checkresiduals(snaive(beer2))`

# training/test set
just using `window` function

# scale-dependent error and percentage errors
scale-dependent means depends on unit, cant used to compare to errors with different unit.
### scaled-dependent:
- MAE: mean absolute error: mean of abs errors
- RMAE: root mean squared error: squared root of mean(error^2)
### pct:
- MAPE: mean absolute percentage error: mean(abs(p[t])), where p[t] is `100*e[t]/y[t]` y[t] is obsevered, e[t] is error at t.

problem of MAPE is y[t] cant be zero or very close to zero, and assume unit can be zero, which not always the case.

- symmetric MAPE
`mean(200*abs(y[t]-yh[t]) / (y[t] +yh[t])': 

also have problem with zero or close to zero

### scaled errors, MASE
training MAE with simple forecast, cant write this math out
```
# traing set
beer2 <- window(ausbeer,start=1992,end=c(2007,4))
# test set
beer3 <- window(ausbeer, start=2008)
# models
beerfit1 <- meanf(beer2,h=10)
beerfit2 <- rwf(beer2,h=10)
beerfit3 <- snaive(beer2,h=10)
accuracy(beerfit1, beer3)
accuracy(beerfit2, beer3)
accuracy(beerfit3, beer3)
```
# cross validation 
`tsCV`
*A good way to choose the best forecasting model is to find the model with the smallest RMSE computed using time series cross-validation.*

```
e <- tsCV(goog200, rwf, drift=TRUE, h=1)
sqrt(mean(e^2, na.rm=TRUE))
sqrt(mean(residuals(rwf(goog200, drift=TRUE))^2, na.rm=TRUE))
```

# prediction intervals, commonly 80% and 95% 
- "h-step",  see `h` in previous sample command., bigger the step in the future, bigger the uncertainty

- non-normal distributed error, using bootstrap, with `bootstrap=TRUE`
