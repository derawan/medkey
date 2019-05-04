<?php
namespace app\modules\medical\application;

use app\common\data\ActiveDataProvider;
use app\common\helpers\CommonHelper;
use app\common\service\ApplicationService;
use app\common\service\exception\AccessApplicationServiceException;
use app\modules\medical\MedicalModule;
use app\modules\medical\models\finders\EhrFilter;
use app\modules\medical\models\orm\Ehr;
use app\modules\medical\models\orm\EhrRecord;
use app\modules\medical\models\form\EhrRecord as EhrRecordForm;
use yii\base\Model;

/**
 * Class EhrApplicationService
 * @package Module\Medical
 * @copyright 2012-2019 Medkey
 */
class EhrService extends ApplicationService implements EhrServiceInterface
{
    /**
     * @inheritdoc
     */
    public function getEhrById($id): Ehr
    {
        return Ehr::find()
            ->notDeleted()
            ->joinWith(['patient.policies.insurance'])
            ->where([
                Ehr::tableColumns('id') => $id,
            ])
            ->one();
    }

    public function createEhrRecord($form)
    {

    }

    public function updateEhrRecord($id, $form)
    {

    }

    public function getEhrRecordFormByRaw($raw)
    {
        $model = EhrRecord::ensureWeak($raw);
        $form = new EhrRecordForm();
        if ($model->isNewRecord) {
            $form->setScenario('create');
        }
        $form->loadAr($model);
        $form->id = $model->id;
        return $form;
    }

    /**
     * @inheritdoc
     */
    public function getEhrList(Model $form): ActiveDataProvider
    {
        /** @var $form EhrFilter */
        if (!$this->isAllowed('getEhrList')) {
            throw new AccessApplicationServiceException(MedicalModule::t('ehr', 'Access restricted'));
        }
        $query = Ehr::find()
            ->andFilterWhere([
                'patient_id' => $form->patientId,
                'cast(updated_at as date)' =>
                    empty($form->updatedAt) ? null : \Yii::$app->formatter->asDate($form->updatedAt, CommonHelper::FORMAT_DATE_DB),
                'type' => $form->type,
            ])
            ->andFilterWhere([
                'like',
                'number',
                $form->number,
            ]);
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getPrivileges()
    {
        return [
            'getEhrList' => MedicalModule::t('ehr', 'EHR list'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function aclAlias()
    {
        return MedicalModule::t('ehr', 'EHR');
    }
}
