function CarPurchaseCost() {
    this.carCostParam = {
        reSetCustom : true,
        //购车价格
        carPrice: 0,
        //首付自定义
        prepaymentCustom: 0,
        //首付比例
        prepaymentPercent: 0.3,
        //还款年限
        loanYears: 1,
        //利率
        rateInterest:0,
        //自定义上牌费用
        licenseTaxCustom: 0,
        //自定义车船使用税
        usageTaxCustom: 0,
        //排量
        displacement: 1.6,
        //座位数
        seatCount: 5,
        //是否进口车
        isImport: 0,
        //第三者责任险 赔付额度
        thirdInsureClaim: 100000,
        //自定义车上人员责任险
        passengerInsureCustom: 0,
        //车身划痕险 赔付额度
        carBodyInsureClaim: 5000,
        //是否勾选
        CommInsureCheck: {
            //第三者责任险
            thirdCheck: true,
            //车辆损失险
            damageCheck: true,
            //全车盗抢险
            stolenCheck: true,
            //玻璃单独破碎险
            glassCheck: true,
            //自燃损失险
            combustCheck: true,
            //不计免赔特约险
            noDeductibleCheck: true,
            //无过责任险
            noLiabilityCheck: true,
            //车上人员责任险
            passengerCheck: true,
            //车身划痕险
            carBodyCheck: true
        }
    }

    this.getPositive = function (val) {
        if (parseFloat(val) < 0) {
            return 0;
        }

        return val;
    }

    //首付款
    this.getPrepayment = function () {
        var carPrice = this.carCostParam.carPrice;
        var percent = this.carCostParam.prepaymentPercent;
        if (!this.carCostParam.reSetCustom && this.carCostParam.prepaymentCustom != 0) {
            return this.carCostParam.prepaymentCustom;
        }

        if (percent == 0) {
            return this.carCostParam.prepaymentCustom;
        }

        return carPrice * percent;
    }

    //贷款额
    this.getBankLoan = function () {
        var _this = this;
        var carPrice = this.carCostParam.carPrice;
        return carPrice - this.getPrepayment();
    }

    //月付款 
    this.getMonthPay = function () {
        var bankLoan = this.getBankLoan(),
        loanYears = this.carCostParam.loanYears;
        months = loanYears * 12,
        rate = 0;
        var rateNum = this.carCostParam.rateInterest;
        console.log(rateNum);
        if(rateNum!=0&&!isNaN(rateNum)){
	       rate = rateNum / 12;
		}else{
			  if (loanYears == 1) {
	            rate = 0.0485 / 12;
	        } else if (loanYears == 2) {
	            rate = 0.0525 / 12;
	        } else if (loanYears == 3) {
	            rate = 0.0525 / 12;
	        } else if (loanYears == 4) {
	            rate = 0.0525 / 12;
	        } else if (loanYears == 5) {
	            rate = 0.0525 / 12;
	        }
		}
        return bankLoan * ((rate * Math.pow(1 + rate, months)) / (Math.pow(1 + rate, months) - 1));
    }


    //购置税
    this.getPurchaseTax = function () {
        var $purchaseTaxEle=$('#txtPurchaseTax').parents('tr').find('.col99');
    	if(this.carCostParam.specistaxexemption > 0){  //是否免税
            $purchaseTaxEle.text('该车型满足购置税免除条件');
        	return 0;
        }
        //if(this.carCostParam.specispreferential > 0){ //是否惠民
        //	var _result=(this.carCostParam.carPrice / 1.17) * 0.1-3000;
        //	return _result>0?_result:0;
        //}
        if(this.carCostParam.displacement<=1.6){
        	$purchaseTaxEle.text('购置税＝购车款/(1+17%)×购置税率(7.5%)');
        	return (this.carCostParam.carPrice / 1.17) * 0.075;
        }
        if(this.carCostParam.specistaxrelief > 0){  // 减税 *0.5
            $purchaseTaxEle.text('购置税＝购车款/(1+17%)×购置税率(10%)x减税(0.5) ');
            return (this.carCostParam.carPrice / 1.17) * 0.1*0.5;
        }
        $purchaseTaxEle.text('购置税＝购车款/(1+17%)×购置税率(10%)');
        return (this.carCostParam.carPrice / 1.17) * 0.1;
    }
    //消费税
    this.getExcise = function () {
        var $exciseEle=$('#txtExcise').parents('tr').find('.col99');
    	if(this.carCostParam.carPrice/1.17 < 1300000){  //是否免税
            $exciseEle.text('该车型满足消费税免除条件');
        	return 0;
        }
        $exciseEle.text('消费税=购车价格/1.17*10%');
        return (this.carCostParam.carPrice / 1.17) * 0.1;
    }
    //上牌费用
    this.getLicenseTax = function () {
        if (!this.carCostParam.reSetCustom) {
            return this.carCostParam.licenseTaxCustom;
        }
        return 500;
    }

    //车船使用税
    this.getUsageTax = function (displacement) {
        var displacement = displacement || this.carCostParam.displacement; //排量
        if (!this.carCostParam.reSetCustom) {
            return this.carCostParam.usageTaxCustom;
        }
        if (displacement <= 1.0) {
            return 300;
        } else if (displacement > 1.0 && displacement <= 1.6) {
            return 420;
        } else if (displacement > 1.6 && displacement <= 2.0) {
            return 480
        } else if (displacement > 2.0 && displacement <= 2.5) {
            return 900
        } else if (displacement > 2.5 && displacement <= 3.0) {
            return 1920
        } else if (displacement > 3.0 && displacement <= 4.0) {
            return 3480
        } else if (displacement > 4.0) {
            return 5280
        }
        return 480;
    }

    //交强险
    this.getTrafficInsurance = function () {
        var seatCount = this.carCostParam.seatCount; //座位数
        if (seatCount < 6) {
            return 950;
        }

        return 1100;
    }

    //第三者责任险
    this.getThirdInsurance = function () {
        //没有选中
        if (!this.carCostParam.CommInsureCheck.thirdCheck) {
            return 0;
        }
        var thirdInsureClaim = this.carCostParam.thirdInsureClaim;
        var seatCount = this.carCostParam.seatCount; //座位数
        if (seatCount < 6) {
            switch (thirdInsureClaim) {
                case 50000:
                    return 516;
                case 100000:
                    return 746;
                case 200000:
                    return 924;
                case 500000:
                    return 1252;
                case 1000000:
                    return 1630;
                default:
                    return 746;
            }
        } else {
            switch (thirdInsureClaim) {
                case 50000:
                    return 478;
                case 100000:
                    return 674;
                case 200000:
                    return 821;
                case 500000:
                    return 1094;
                case 1000000:
                    return 1425;
                default:
                    return 674;
            }
        }
        return 746;
    }

    //车辆损失险
    this.getDamageInsurance = function () {
        //没有选中
        if (!this.carCostParam.CommInsureCheck.damageCheck) {
            return 0;
        }
        var carPrice = this.carCostParam.carPrice;
        var seatCount = this.carCostParam.seatCount; //座位数
        var base = 459;
        if (seatCount >= 6) {
            base = 550;
        }
        return base + carPrice * 0.01088;
    }

    //全车盗抢险
    this.getStolenInsurance = function () {
        //没有选中
        if (!this.carCostParam.CommInsureCheck.stolenCheck) {
            return 0;
        }
        var carPrice = this.carCostParam.carPrice;
        var seatCount = this.carCostParam.seatCount; //座位数       
        if (seatCount >= 6) {
            return 119 + carPrice * 0.00374;
        } else {
            return 102 + carPrice * 0.004505;
        }        
    }

    //玻璃单独破碎险
    this.getGlassInsurance = function () {
        //没有选中
        if (!this.carCostParam.CommInsureCheck.glassCheck) {
            return 0;
        }
        var carPrice = this.carCostParam.carPrice;
        var isImport = this.carCostParam.isImport;
        if (isImport == 1) {
            return carPrice * 0.0025;
        } else {
            return carPrice * 0.0015;
        }
    }

    //自燃损失险
    this.getCombustInsurance = function () {
        //没有选中
        if (!this.carCostParam.CommInsureCheck.combustCheck) {
            return 0;
        }
        var carPrice = this.carCostParam.carPrice;
        return carPrice * 0.0015;
    }

    //不计免赔特约险
    this.getNoDeductibleInsurance = function () {
        //没有选中
        if (!this.carCostParam.CommInsureCheck.noDeductibleCheck) {
            return 0;
        }
        var damageInsurance = this.getDamageInsurance(),
        thirdInsurance = this.getThirdInsurance();
        if (damageInsurance == undefined || thirdInsurance == undefined) {
            return 0;
        }

        return (damageInsurance + thirdInsurance) * 0.2;
    }

    //无过责任险
    this.getNoLiabilityInsurance = function () {
        //没有选中
        if (!this.carCostParam.CommInsureCheck.noLiabilityCheck) {
            return 0;
        }
        var thirdInsurance = this.getThirdInsurance();
        if (thirdInsurance == undefined) {
            return 0;
        }

        return (thirdInsurance) * 0.2;
    }

    //车上人员责任险
    this.getPassengerInsurance = function () {
        //没有选中
        if (!this.carCostParam.CommInsureCheck.passengerCheck) {
            return 0;
        }
        if (!this.carCostParam.reSetCustom) {
            return this.carCostParam.passengerInsureCustom;
        }
        return 50;
    }

    //车身划痕险
    this.getCarBodyInsurance = function () {
        //没有选中
        if (!this.carCostParam.CommInsureCheck.carBodyCheck) {
            return 0;
        }
        var carPrice = this.carCostParam.carPrice;
        var carBodyInsureClaim = this.carCostParam.carBodyInsureClaim; //赔付额度
        if (carBodyInsureClaim == 2000) {
            if (carPrice > 0 && carPrice <= 300000) {
                return 400
            }
            if (carPrice > 300000 && carPrice <= 500000) {
                return 585
            }
            if (carPrice > 500000) {
                return 850
            }
            return 0;

        } else if (carBodyInsureClaim == 5000) {
            if (carPrice > 0 && carPrice <= 300000) {
                return 570
            }
            if (carPrice > 300000 && carPrice <= 500000) {
                return 900
            }
            if (carPrice > 500000) {
                return 1100
            }
            return 0;
        } else if (carBodyInsureClaim == 10000) {
            if (carPrice > 0 && carPrice <= 300000) {
                return 760
            }
            if (carPrice > 300000 && carPrice <= 500000) {
                return 1170
            }
            if (carPrice > 500000) {
                return 1500
            }
            return 0;
        } else if (carBodyInsureClaim == 20000) {
            if (carPrice > 0 && carPrice <= 300000) {
                return 1140
            }
            if (carPrice > 300000 && carPrice <= 500000) {
                return 1780
            }
            if (carPrice > 500000) {
                return 2250
            }
            return 0;
        }

        return 0;
    }
}

CarPurchaseCost.prototype.getCarPurchaseCost = function (costParam) {
    //    var getPrepayment, getBankLoan, getMonthPay, getPurchaseTax, getExcise, getLicenseTax, getUsageTax, getTrafficInsurance, getThirdInsurance,
    //    getDamageInsurance, getStolenInsurance, getGlassInsurance, getCombustInsurance, getNoDeductibleInsurance, getNoLiabilityInsurance,
    //    getPassengerInsurance, getCarBodyInsurance, 
    var carLoanFee, carPurchaseTax, carExcise, carInsurance, carPurchaseFee;
    //赋值
    for (key in costParam) {
        if (costParam[key] !== undefined) {
            this.carCostParam[key] = costParam[key]
        }
    }

    carLoanFee = {
        //贷款年限
        years: 1,
        //贷款月数
        months: 12,
        //首付
        prepayment: 0,
        //贷款额
        bankLoan: 0,
        //月付款
        monthPay: 0,
        //还贷总额
        getRepayment: function () {
            return this.monthPay * this.months;
        }
    }

    carPurchaseTax = {
        //购置税
        purchaseTax: 0,
        //消费税
        excise: 0,
        //上牌费用
        licenseTax: 0,
        //车船使用税
        usageTax: 0,
        //税用综合
        getTotal: function () {
            return this.purchaseTax + this.excise + this.licenseTax + this.usageTax;
        }
    }

    carInsurance = {
        //交通事故责任强制保险
        trafficInsurance: 0,
        //第三者责任险
        thirdInsurance: 0,
        //车辆损失险
        damageInsurance: 0,
        //全车盗抢险
        stolenInsurance: 0,
        //玻璃单独破碎险
        glassInsurance: 0,
        //自燃损失险
        combustInsurance: 0,
        //不计免赔特约险
        noDeductibleInsurance: 0,
        //无过责任险
        noLiabilityInsurance: 0,
        //车上人员责任险
        passengerInsurance: 0,
        //车身划痕险
        carBodyInsurance: 0,
        //商业险总和
        getCommerceTotal: function () {
            return this.thirdInsurance + this.damageInsurance + this.stolenInsurance + this.glassInsurance + this.combustInsurance + this.noDeductibleInsurance +
            this.noLiabilityInsurance + this.passengerInsurance + this.carBodyInsurance;
        },
        //保险总和
        getInsuranceTotal: function () {
            return this.trafficInsurance + this.thirdInsurance + this.damageInsurance + this.stolenInsurance + this.glassInsurance + this.combustInsurance + this.noDeductibleInsurance +
            this.noLiabilityInsurance + this.passengerInsurance + this.carBodyInsurance;
        }
    }

    carPurchaseFee = {
        carPrice: 0,
        carLoanFee: carLoanFee,
        carPurchaseTax: carPurchaseTax,
        carExcise: carExcise,
        carInsurance: carInsurance,
        //商业保险总费用
        getCommerceInsurance: function () {
            return this.carInsurance.getCommerceTotal();
        },
        //首期付款额
        getTotalPrepayment: function () {
            return this.carLoanFee.prepayment + this.carPurchaseTax.getTotal() + this.carInsurance.getInsuranceTotal();
        },
        //全款购车预计花费
        getTotal: function () {
            return this.carPrice + this.carPurchaseTax.getTotal() + this.carInsurance.getInsuranceTotal();
        },
        //贷款买车总花费
        getTotalLoan: function () {
            return this.carLoanFee.prepayment + this.carLoanFee.getRepayment() + this.carPurchaseTax.getTotal() + this.carInsurance.getInsuranceTotal();
        },
        //贷款买车比全款买车多花费
        getLoanMoreCost: function () {
            if (this.getTotalLoan() < this.getTotal()) {
                return 0;
            }
            return this.getTotalLoan() - this.getTotal();
        },
        //必要花费综合
        getTotalTax: function () {
            return this.carPurchaseTax.getTotal() + this.carInsurance.trafficInsurance;
        },
        //公司报价
        getTotalInsurance: function () {
            return carInsurance.trafficInsurance + this.carInsurance.getCommerceTotal();
        },
        //市场价
        getMarketCommerce: function () {
            return carInsurance.trafficInsurance + Math.round((this.carInsurance.getCommerceTotal() * 0.9));
        }
    }




    carPurchaseFee.carPrice = this.getPositive(this.carCostParam.carPrice); //购车价格
    carPurchaseFee.carLoanFee.years = this.getPositive(this.carCostParam.loanYears); //还款年数
    carPurchaseFee.carLoanFee.months = this.getPositive(this.carCostParam.loanYears * 12); //还款月数
    carPurchaseFee.carLoanFee.prepayment = this.getPositive(Math.round(this.getPrepayment())); //首付款
    carPurchaseFee.carLoanFee.bankLoan = this.getPositive(Math.round(this.getBankLoan())); //贷款额
    carPurchaseFee.carLoanFee.monthPay = this.getPositive(Math.round(this.getMonthPay())); //月付款
    carPurchaseFee.carPurchaseTax.purchaseTax = this.getPositive(Math.round(this.getPurchaseTax())); //购置税
    carPurchaseFee.carPurchaseTax.excise = this.getPositive(Math.round(this.getExcise()));//消费税
    carPurchaseFee.carPurchaseTax.licenseTax = this.getPositive(Math.round(this.getLicenseTax())); //购置税
    carPurchaseFee.carPurchaseTax.usageTax = this.getPositive(Math.round(this.getUsageTax())); //车船使用税
    carPurchaseFee.carInsurance.trafficInsurance = this.getPositive(Math.round(this.getTrafficInsurance())); // 交强险
    carPurchaseFee.carInsurance.thirdInsurance = this.getPositive(Math.round(this.getThirdInsurance())); //第三者责任险
    carPurchaseFee.carInsurance.damageInsurance = this.getPositive(Math.round(this.getDamageInsurance())); //车辆损失险
    carPurchaseFee.carInsurance.stolenInsurance = this.getPositive(Math.round(this.getStolenInsurance())); //全车盗抢险
    carPurchaseFee.carInsurance.glassInsurance = this.getPositive(Math.round(this.getGlassInsurance())); //玻璃单独破碎险
    carPurchaseFee.carInsurance.combustInsurance = this.getPositive(Math.round(this.getCombustInsurance())); //自燃损失险
    carPurchaseFee.carInsurance.noDeductibleInsurance = this.getPositive(Math.round(this.getNoDeductibleInsurance())); //不计免赔特约险
    carPurchaseFee.carInsurance.noLiabilityInsurance = this.getPositive(Math.round(this.getNoLiabilityInsurance())); //无过责任险
    carPurchaseFee.carInsurance.passengerInsurance = this.getPositive(Math.round(this.getPassengerInsurance())); //车上人员责任险
    carPurchaseFee.carInsurance.carBodyInsurance = this.getPositive(Math.round(this.getCarBodyInsurance())); //车身划痕险

    return carPurchaseFee;

}


